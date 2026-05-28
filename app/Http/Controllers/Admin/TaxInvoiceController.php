<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
use App\Models\TaxInvoice;
use App\Models\TaxInvoiceItem;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaxInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = TaxInvoice::with(['customer', 'project'])
            ->where('company_id', $companyId)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('year')) {
            $year = (int) $request->year;
            if ($year >= 2000 && $year <= 2100) {
                $query->whereYear('invoice_date', $year);
            }
        }
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($qb) use ($q) {
                $qb->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('buyer_name', 'like', "%{$q}%");
            });
        }

        $invoices = $query->paginate(20)->withQueryString();
        $company = Company::findOrFail($companyId);
        $filterYears = $this->taxInvoiceFilterYears($companyId);

        if ($request->ajax()) {
            return view('admin.tax_invoices.partials.index-list', compact('invoices'));
        }

        return view('admin.tax_invoices.index', compact('invoices', 'company', 'filterYears'));
    }

    public function create(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->route('admin.tax-invoices.index', ['modal' => 'create']);
        }

        return view('admin.tax_invoices.partials.modal-form', array_merge(
            $this->formData(),
            ['taxInvoice' => null]
        ));
    }

    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::findOrFail($companyId);
        $validated = $this->validateInvoice($request, $companyId);

        DB::beginTransaction();
        try {
            $totals = TaxInvoice::calculateTotals(
                $validated['items'],
                (float) ($validated['discount_percent'] ?? 0),
                (float) ($validated['discount_amount'] ?? 0),
                (float) ($validated['vat_percent'] ?? $company->default_vat_percent ?? 13)
            );

            $invoice = new TaxInvoice([
                'company_id' => $companyId,
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'transaction_date_bs' => $validated['transaction_date_bs'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'buyer_name' => $validated['buyer_name'],
                'buyer_address' => $validated['buyer_address'] ?? null,
                'buyer_pan' => $validated['buyer_pan'] ?? null,
                'buyer_phone' => $validated['buyer_phone'] ?? null,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $totals['subtotal'],
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'discount_amount' => $totals['discount'],
                'taxable_amount' => $totals['taxable'],
                'vat_percent' => $validated['vat_percent'] ?? $company->default_vat_percent ?? 13,
                'vat_amount' => $totals['vat'],
                'grand_total' => $totals['grand'],
                'template' => $validated['template'] ?? $company->vat_bill_template ?? 'nepali_annex5',
                'status' => $validated['status'] ?? 'draft',
                'notes' => $validated['notes'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $invoice->refreshAmountInWords();
            $invoice->save();

            $this->syncItems($invoice, $validated['items']);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tax invoice (कर विजक) saved successfully.',
                    'invoice_id' => $invoice->id,
                ]);
            }

            return redirect()->route('admin.tax-invoices.show', $invoice)
                ->with('success', 'Tax invoice (कर विजक) saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to save: '.$e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function show(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);
        $taxInvoice->load(['items', 'customer', 'project', 'company', 'creator']);
        $company = $taxInvoice->company;
        $printExtras = $this->printExtras($company, $taxInvoice);

        if ($request->ajax()) {
            return view('admin.tax_invoices.partials.show-detail', array_merge(
                compact('taxInvoice', 'company'),
                $printExtras
            ));
        }

        return redirect()->route('admin.tax-invoices.index', ['view' => $taxInvoice->id]);
    }

    public function print(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);

        if (! $taxInvoice->isPrintable()) {
            abort(403, 'Only issued invoices can be printed. Set status to Issued first.');
        }

        $data = $this->printViewData($taxInvoice, $request);

        if ($request->boolean('embed')) {
            return response()
                ->view('admin.tax_invoices.print-embed', $data)
                ->header('Cache-Control', 'private, max-age=300');
        }

        return view('admin.tax_invoices.print', $data);
    }

    public function edit(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);

        if (! $taxInvoice->isEditable()) {
            return $this->notEditableResponse($request);
        }

        $taxInvoice->load('items');

        if (! $request->ajax()) {
            return redirect()->route('admin.tax-invoices.index', ['edit' => $taxInvoice->id]);
        }

        return view('admin.tax_invoices.partials.modal-form', array_merge(
            ['taxInvoice' => $taxInvoice],
            $this->formData()
        ));
    }

    public function update(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);

        if (! $taxInvoice->isEditable()) {
            return $this->notEditableResponse($request);
        }

        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::findOrFail($companyId);
        $validated = $this->validateInvoice($request, $companyId, $taxInvoice->id);

        DB::beginTransaction();
        try {
            $totals = TaxInvoice::calculateTotals(
                $validated['items'],
                (float) ($validated['discount_percent'] ?? 0),
                (float) ($validated['discount_amount'] ?? 0),
                (float) ($validated['vat_percent'] ?? $company->default_vat_percent ?? 13)
            );

            $taxInvoice->fill([
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'transaction_date_bs' => $validated['transaction_date_bs'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'buyer_name' => $validated['buyer_name'],
                'buyer_address' => $validated['buyer_address'] ?? null,
                'buyer_pan' => $validated['buyer_pan'] ?? null,
                'buyer_phone' => $validated['buyer_phone'] ?? null,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $totals['subtotal'],
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'discount_amount' => $totals['discount'],
                'taxable_amount' => $totals['taxable'],
                'vat_percent' => $validated['vat_percent'] ?? 13,
                'vat_amount' => $totals['vat'],
                'grand_total' => $totals['grand'],
                'template' => $validated['template'] ?? $taxInvoice->template,
                'status' => $validated['status'] ?? $taxInvoice->status,
                'notes' => $validated['notes'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'updated_by' => auth()->id(),
            ]);
            $taxInvoice->refreshAmountInWords();
            $taxInvoice->save();

            $taxInvoice->items()->delete();
            $this->syncItems($taxInvoice, $validated['items']);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tax invoice updated successfully.',
                    'invoice_id' => $taxInvoice->id,
                ]);
            }

            return redirect()->route('admin.tax-invoices.show', $taxInvoice)
                ->with('success', 'Tax invoice updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to update: '.$e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);

        $validated = $request->validate([
            'status' => 'required|in:draft,issued,cancelled',
        ]);

        $taxInvoice->update([
            'status' => $validated['status'],
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Status updated to '.ucfirst($validated['status']).'.',
            'status' => $taxInvoice->status,
        ]);
    }

    public function destroy(Request $request, TaxInvoice $taxInvoice)
    {
        $this->authorizeInvoice($taxInvoice);

        if (! $taxInvoice->isEditable()) {
            return $this->notEditableResponse($request);
        }

        $taxInvoice->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tax invoice deleted.']);
        }

        return redirect()->route('admin.tax-invoices.index')
            ->with('success', 'Tax invoice deleted.');
    }

    public function settings(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::findOrFail($companyId);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'vat_bill_template' => 'required|in:nepali_annex5,english_standard',
                'vat_bill_accent_color' => 'nullable|string|max:20',
                'default_vat_percent' => 'required|numeric|min:0|max:100',
                'vat_bill_footer_text' => 'nullable|string|max:255',
            ]);

            $company->update($validated);

            return back()->with('success', 'VAT bill design settings saved for this company.');
        }

        return view('admin.tax_invoices.settings', compact('company'));
    }

    private function formData(): array
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::findOrFail($companyId);

        return [
            'company' => $company,
            'customers' => Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::where('company_id', $companyId)->where('status', '!=', 'cancelled')->orderBy('name')->get(),
            'suggestedNumber' => TaxInvoice::suggestInvoiceNumber($companyId),
            'templates' => TaxInvoice::templateOptions(),
            'paymentMethods' => TaxInvoice::paymentMethodOptions(),
        ];
    }

    private function validateInvoice(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        $uniqueRule = Rule::unique('tax_invoices', 'invoice_number')
            ->where('company_id', $companyId);
        if ($ignoreId) {
            $uniqueRule->ignore($ignoreId);
        }

        return $request->validate([
            'invoice_number' => ['required', 'string', 'max:50', $uniqueRule],
            'invoice_date' => 'required|date',
            'transaction_date_bs' => 'nullable|string|max:20',
            'customer_id' => 'nullable|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'buyer_name' => 'required|string|max:255',
            'buyer_address' => 'nullable|string',
            'buyer_pan' => 'nullable|string|max:20',
            'buyer_phone' => 'nullable|string|max:50',
            'payment_method' => 'required|in:cash,cheque,credit,other',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'template' => 'nullable|in:nepali_annex5,english_standard',
            'status' => 'nullable|in:draft,issued,cancelled',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.hs_code' => 'nullable|string|max:30',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'nullable|string|max:30',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function syncItems(TaxInvoice $invoice, array $items): void
    {
        foreach ($items as $index => $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            TaxInvoiceItem::create([
                'tax_invoice_id' => $invoice->id,
                'line_number' => $index + 1,
                'hs_code' => $item['hs_code'] ?? null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $price,
                'line_amount' => round($qty * $price, 2),
            ]);
        }
    }

    private function printViewData(TaxInvoice $taxInvoice, Request $request): array
    {
        $relations = ['items', 'company'];
        if (! $request->boolean('embed')) {
            $relations = array_merge($relations, ['customer', 'project', 'creator']);
        }

        $taxInvoice->load($relations);

        $company = $taxInvoice->company ?? Company::findOrFail(CompanyContext::getActiveCompanyId());

        return array_merge(
            ['taxInvoice' => $taxInvoice, 'company' => $company],
            $this->printExtras($company, $taxInvoice)
        );
    }

    private function printExtras(Company $company, TaxInvoice $taxInvoice): array
    {
        return [
            'companyLogoUrl' => $company->logo ? asset('storage/'.$company->logo) : null,
            'preparedByName' => ($taxInvoice->relationLoaded('creator') && $taxInvoice->creator)
                ? $taxInvoice->creator->name
                : (auth()->user()->name ?? '—'),
        ];
    }

    /** @return list<int> */
    private function taxInvoiceFilterYears(int $companyId): array
    {
        $years = TaxInvoice::query()
            ->where('company_id', $companyId)
            ->whereNotNull('invoice_date')
            ->selectRaw('YEAR(invoice_date) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn ($y) => (int) $y)
            ->filter(fn ($y) => $y >= 2000 && $y <= 2100)
            ->values();

        $currentYear = (int) now()->format('Y');

        if (! $years->contains($currentYear)) {
            $years->prepend($currentYear);
        }

        return $years->unique()->sortDesc()->values()->all();
    }

    private function authorizeInvoice(TaxInvoice $taxInvoice): void
    {
        if ($taxInvoice->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
    }

    private function notEditableResponse(Request $request)
    {
        $message = 'Only draft invoices can be edited. Change status to Draft first, or duplicate as a new invoice.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('admin.tax-invoices.index')->with('error', $message);
    }
}
