<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Exports\RunningBillExport;
use App\Models\MeasurementBookItem;
use App\Models\RunningBill;
use App\Models\RunningBillItem;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RunningBillController extends Controller
{
    use HasProjectAccess;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = RunningBill::with(['project', 'creator'])
            ->where('company_id', $companyId);

        $this->filterByAccessibleProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if (!$this->canAccessProject($projectId)) {
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
        }

        $bills = $query->latest('bill_date')->paginate(15)->withQueryString();
        $projects = $this->getAccessibleProjects();

        return view('admin.running_bills.index', compact('bills', 'projects'));
    }

    public function create(Request $request)
    {
        $projects = $this->getAccessibleProjects();
        return view('admin.running_bills.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'contract_no' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'bill_title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.boq_qty' => 'nullable|numeric|min:0',
            'items.*.boq_unit_price' => 'nullable|numeric|min:0',
            'items.*.this_bill_qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $this->authorizeProjectAccess((int) $validated['project_id']);

        $bill = RunningBill::create([
            'company_id' => CompanyContext::getActiveCompanyId(),
            'project_id' => $validated['project_id'],
            'contract_no' => $validated['contract_no'] ?? null,
            'bill_date' => $validated['bill_date'],
            'bill_title' => $validated['bill_title'],
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $i => $it) {
            $tbill = ((float)($it['this_bill_qty'] ?? 0)) * ((float)($it['unit_price'] ?? 0));
            $boq = (float)($it['boq_qty'] ?? 0);
            $rem = $boq - (float)($it['this_bill_qty'] ?? 0);

            RunningBillItem::create([
                'running_bill_id' => $bill->id,
                'sn' => $i + 1,
                'description' => $it['description'],
                'unit' => $it['unit'] ?? null,
                'boq_qty' => $boq,
                'boq_unit_price' => (float)($it['boq_unit_price'] ?? 0),
                'this_bill_qty' => (float)($it['this_bill_qty'] ?? 0),
                'unit_price' => (float)($it['unit_price'] ?? 0),
                'total_price' => $tbill,
                'remaining_qty' => $rem,
                'remarks' => $it['remarks'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.running-bills.show', $bill)
            ->with('success', 'Running Bill created successfully.');
    }

    public function show(RunningBill $running_bill)
    {
        $this->authorizeProjectAccess((int) $running_bill->project_id);
        $running_bill->load(['company', 'project', 'items', 'creator']);
        $subtotal = $running_bill->items->sum('total_price');
        $taxPercent = 13;
        $taxAmount = round($subtotal * ($taxPercent / 100), 2);
        $total = $subtotal + $taxAmount;
        return view('admin.running_bills.show', compact('running_bill', 'subtotal', 'taxPercent', 'taxAmount', 'total'));
    }

    public function edit(RunningBill $running_bill)
    {
        $this->authorizeProjectAccess((int) $running_bill->project_id);
        $running_bill->load('items');
        $projects = $this->getAccessibleProjects();
        return view('admin.running_bills.edit', compact('running_bill', 'projects'));
    }

    public function update(Request $request, RunningBill $running_bill)
    {
        $this->authorizeProjectAccess((int) $running_bill->project_id);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'contract_no' => 'nullable|string|max:255',
            'bill_date' => 'required|date',
            'bill_title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.boq_qty' => 'nullable|numeric|min:0',
            'items.*.boq_unit_price' => 'nullable|numeric|min:0',
            'items.*.this_bill_qty' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $running_bill->update([
            'project_id' => $validated['project_id'],
            'contract_no' => $validated['contract_no'] ?? null,
            'bill_date' => $validated['bill_date'],
            'bill_title' => $validated['bill_title'],
        ]);

        $running_bill->items()->delete();
        foreach ($validated['items'] as $i => $it) {
            $tbill = ((float)($it['this_bill_qty'] ?? 0)) * ((float)($it['unit_price'] ?? 0));
            $boq = (float)($it['boq_qty'] ?? 0);
            $rem = $boq - (float)($it['this_bill_qty'] ?? 0);

            RunningBillItem::create([
                'running_bill_id' => $running_bill->id,
                'sn' => $i + 1,
                'description' => $it['description'],
                'unit' => $it['unit'] ?? null,
                'boq_qty' => $boq,
                'boq_unit_price' => (float)($it['boq_unit_price'] ?? 0),
                'this_bill_qty' => (float)($it['this_bill_qty'] ?? 0),
                'unit_price' => (float)($it['unit_price'] ?? 0),
                'total_price' => $tbill,
                'remaining_qty' => $rem,
                'remarks' => $it['remarks'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.running-bills.show', $running_bill)
            ->with('success', 'Running Bill updated successfully.');
    }

    public function destroy(RunningBill $running_bill)
    {
        $this->authorizeProjectAccess((int) $running_bill->project_id);
        $running_bill->delete();
        return redirect()->route('admin.running-bills.index')
            ->with('success', 'Running Bill deleted successfully.');
    }

    public function exportExcel(RunningBill $running_bill)
    {
        $this->authorizeProjectAccess((int) $running_bill->project_id);
        $name = 'running_bill_' . $running_bill->id . '_' . ($running_bill->bill_date ? $running_bill->bill_date->format('Y-m-d') : 'export');
        return Excel::download(new RunningBillExport($running_bill), $name . '.xlsx');
    }

    /**
     * Measurement Book बाट यस project को work description, unit, quantity ल्याउन (Running Bill मा Load from Measurement Book).
     */
    public function getMeasurementBookItems(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        $projectId = (int) $request->project_id;
        $this->authorizeProjectAccess($projectId);

        $companyId = CompanyContext::getActiveCompanyId();

        // Note: We don't filter out existing items here anymore - frontend will handle add/update logic
        // This allows updating quantities when measurement book items are modified

        // Only get main works (parent_id is null) - sub-works should not be included
        $rows = MeasurementBookItem::with('children')
            ->join('measurement_books', 'measurement_book_items.measurement_book_id', '=', 'measurement_books.id')
            ->where('measurement_books.project_id', $projectId)
            ->where('measurement_books.company_id', $companyId)
            ->whereNull('measurement_book_items.parent_id') // Only main works
            ->select('measurement_book_items.*', 'measurement_books.measurement_date')
            ->orderBy('measurement_books.measurement_date')
            ->orderBy('measurement_book_items.sn')
            ->get();

        $items = $rows
            ->map(function ($r) {
                // Calculate total_qty: if main work has children, sum their quantities; otherwise use main work's quantity or total_qty
                $qty = 0;
                $description = trim((string)($r->works ?? ''));
                $unit = trim((string)($r->unit ?? ''));
                
                if ($r->children && $r->children->count() > 0) {
                    // Main work has sub-works: use sum of sub-work quantities
                    $qty = (float) $r->children->sum('quantity');
                    
                    // If main work's description or unit is empty, try to get from first sub-work
                    if ($description === '' || $unit === '') {
                        $firstChild = $r->children->first();
                        if ($firstChild) {
                            if ($description === '') {
                                $description = trim((string)($firstChild->works ?? ''));
                            }
                            if ($unit === '') {
                                $unit = trim((string)($firstChild->unit ?? ''));
                            }
                        }
                    }
                } else {
                    // Main work has no sub-works: use total_qty if available, otherwise quantity
                    $qty = $r->total_qty !== null && (string)$r->total_qty !== '' && (float)$r->total_qty > 0 
                        ? (float) $r->total_qty 
                        : ((float) ($r->quantity ?? 0) > 0 ? (float) ($r->quantity ?? 0) : 0);
                }
                
                return [
                    'description' => $description,
                    'unit' => $unit,
                    'boq_qty' => '0',
                    'boq_unit_price' => '0',
                    'this_bill_qty' => $qty > 0 ? (string) round($qty, 4) : '0',
                    'unit_price' => '0',
                ];
            })
            ->filter(function ($it) {
                // Only filter out items with no description/unit/quantity - don't filter existing items
                // Frontend will handle add/update logic based on description+unit match
                $d = trim((string) $it['description']);
                $u = trim((string) $it['unit']);
                if ($d === '' || $u === '' || (float)($it['this_bill_qty'] ?? 0) <= 0) {
                    return false;
                }
                return true;
            })
            ->values()
            ->all();

        return response()->json(['items' => $items]);
    }
}
