<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Subcontractor;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class SubcontractorController extends Controller
{
    use HasProjectAccess;

    public function __construct()
    {
        $this->middleware('admin');
    }

    protected function assertCompanySubcontractor(Subcontractor $subcontractor): void
    {
        if ($subcontractor->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Subcontractor::where('company_id', $companyId)->orderBy('name');

        if ($request->filled('keyword')) {
            $kw = '%' . trim($request->keyword) . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', $kw)
                    ->orWhere('phone', 'like', $kw)
                    ->orWhere('email', 'like', $kw)
                    ->orWhere('contact_person', 'like', $kw);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $subcontractors = $query->paginate(15)->withQueryString();

        $workTypeOptions = Subcontractor::workTypeOptions();

        return view('admin.subcontractors.index', compact('subcontractors', 'workTypeOptions'));
    }

    public function create(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.subcontractors.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'work_types' => 'nullable|array',
            'work_types.*' => 'string|max:100',
            'work_types_custom' => 'nullable|string|max:2000',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['work_types'] = $this->normalizedWorkTypes($request);

        $subcontractor = Subcontractor::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-contractor created successfully.',
                'subcontractor' => $this->subcontractorJsonForList($subcontractor),
            ]);
        }

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Sub-contractor created successfully.');
    }

    public function show(Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        $companyId = CompanyContext::getActiveCompanyId();

        $expensesQuery = Expense::withoutGlobalScope('project')
            ->with(['category', 'subcategory', 'project', 'expenseType'])
            ->where('company_id', $companyId)
            ->where('subcontractor_id', $subcontractor->id);

        $this->filterByAccessibleProjects($expensesQuery, 'project_id');

        $expenses = $expensesQuery->orderByDesc('date')->orderByDesc('id')->paginate(10)->withQueryString();

        $projects = $this->getAccessibleProjects();

        return view('admin.subcontractors.show', compact(
            'subcontractor',
            'expenses',
            'projects'
        ));
    }

    /**
     * JSON for the list-page modal: profile + payment form dropdowns + recent expenses.
     */
    public function paymentModalData(Request $request, Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        $companyId = CompanyContext::getActiveCompanyId();

        $expensesQuery = Expense::withoutGlobalScope('project')
            ->with(['category', 'subcategory', 'project'])
            ->where('company_id', $companyId)
            ->where('subcontractor_id', $subcontractor->id);

        $this->filterByAccessibleProjects($expensesQuery, 'project_id');

        $paymentsTotal = (clone $expensesQuery)->sum('amount');

        $recentExpenses = $expensesQuery->orderByDesc('date')->orderByDesc('id')->limit(15)->get();

        $projects = $this->getAccessibleProjects();

        return response()->json([
            'payments_total' => number_format((float) $paymentsTotal, 2),
            'subcontractor' => [
                'id' => $subcontractor->id,
                'name' => $subcontractor->name,
                'contact_person' => $subcontractor->contact_person,
                'phone' => $subcontractor->phone,
                'email' => $subcontractor->email,
                'pan_number' => $subcontractor->pan_number,
                'address' => $subcontractor->address,
                'notes' => $subcontractor->notes,
                'work_types' => $this->workTypesArrayForResponse($subcontractor),
                'is_active' => $subcontractor->is_active,
            ],
            'projects' => $projects->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values(),
            'recent_expenses' => $recentExpenses->map(function ($e) {
                return [
                    'date_display' => $e->date->format('M d, Y'),
                    'project' => $e->project?->name,
                    'category_line' => $e->category->name.($e->subcategory ? ' / '.$e->subcategory->name : ''),
                    'amount' => number_format((float) $e->amount, 2),
                ];
            })->values(),
            'urls' => [
                'full_show' => route('admin.subcontractors.show', $subcontractor),
                'expenses' => route('admin.expenses.index', ['subcontractor_id' => $subcontractor->id]),
                'edit' => route('admin.subcontractors.index', ['edit' => $subcontractor->id]),
            ],
        ]);
    }

    public function edit(Request $request, Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'subcontractor' => [
                    'id' => $subcontractor->id,
                    'name' => $subcontractor->name,
                    'phone' => $subcontractor->phone,
                    'email' => $subcontractor->email,
                    'contact_person' => $subcontractor->contact_person,
                    'pan_number' => $subcontractor->pan_number,
                    'address' => $subcontractor->address,
                    'notes' => $subcontractor->notes,
                    'work_types' => $this->workTypesArrayForResponse($subcontractor),
                    'is_active' => $subcontractor->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.subcontractors.index');
    }

    public function update(Request $request, Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'work_types' => 'nullable|array',
            'work_types.*' => 'string|max:100',
            'work_types_custom' => 'nullable|string|max:2000',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['work_types'] = $this->normalizedWorkTypes($request);

        $subcontractor->update($validated);
        $subcontractor->refresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-contractor updated successfully.',
                'subcontractor' => $this->subcontractorJsonForList($subcontractor),
            ]);
        }

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Sub-contractor updated successfully.');
    }

    public function destroy(Request $request, Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        if ($subcontractor->expenses()->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete: this sub-contractor has linked expense payments. Remove or reassign them first.',
                ], 422);
            }

            return redirect()->route('admin.subcontractors.index')
                ->with('error', 'Cannot delete: this sub-contractor has linked expense payments. Remove or reassign them first.');
        }

        $subcontractor->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-contractor deleted successfully.',
            ]);
        }

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Sub-contractor deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function subcontractorJsonForList(Subcontractor $subcontractor): array
    {
        return [
            'id' => $subcontractor->id,
            'name' => $subcontractor->name,
            'phone' => $subcontractor->phone,
            'email' => $subcontractor->email,
            'contact_person' => $subcontractor->contact_person,
            'pan_number' => $subcontractor->pan_number,
            'address' => $subcontractor->address,
            'notes' => $subcontractor->notes,
            'work_types' => $this->workTypesArrayForResponse($subcontractor),
            'is_active' => $subcontractor->is_active,
        ];
    }

    /**
     * Always return a list of strings for JSON (handles cast quirks / legacy string JSON).
     *
     * @return list<string>
     */
    protected function workTypesArrayForResponse(Subcontractor $subcontractor): array
    {
        $wt = $subcontractor->work_types;

        if ($wt === null || $wt === '') {
            return [];
        }

        if (is_array($wt)) {
            return array_values(array_filter(array_map('strval', $wt)));
        }

        if (is_string($wt)) {
            $decoded = json_decode($wt, true);

            return is_array($decoded) ? array_values(array_filter(array_map('strval', $decoded))) : [];
        }

        return [];
    }

    /**
     * Merge preset checkboxes and custom text into a unique list (max 50 entries).
     */
    protected function normalizedWorkTypes(Request $request): ?array
    {
        $selected = $request->input('work_types', []);
        if (! is_array($selected)) {
            $selected = [];
        }
        $selected = array_values(array_unique(array_filter(array_map(
            static fn ($s) => mb_substr(trim((string) $s), 0, 100),
            $selected
        ))));

        $customRaw = (string) $request->input('work_types_custom', '');
        $customParts = preg_split('/[\n,;]+/u', $customRaw, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($customParts as $part) {
            $t = mb_substr(trim($part), 0, 100);
            if ($t !== '') {
                $selected[] = $t;
            }
        }

        $selected = array_values(array_unique($selected));

        $presetByLower = [];
        foreach (Subcontractor::workTypeOptions() as $label) {
            $presetByLower[mb_strtolower($label)] = $label;
        }
        $selected = array_values(array_unique(array_map(function ($s) use ($presetByLower) {
            $lower = mb_strtolower($s);

            return $presetByLower[$lower] ?? $s;
        }, $selected)));

        $selected = array_slice($selected, 0, 50);

        return $selected === [] ? null : $selected;
    }

    /**
     * Record a payment as an expense linked to this sub-contractor.
     */
    public function storePayment(Request $request, Subcontractor $subcontractor)
    {
        $this->assertCompanySubcontractor($subcontractor);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->authorizeProjectAccess((int) $validated['project_id']);

        $companyId = CompanyContext::getActiveCompanyId();
        $projectOk = Project::where('company_id', $companyId)->whereKey($validated['project_id'])->exists();
        if (! $projectOk) {
            return $this->paymentValidationError($request, 'project_id', 'Invalid project for this company.');
        }

        $defaultCategory = Category::firstOrCreateDefaultExpense();

        $defaultExpenseType = ExpenseType::orderBy('name')->first();

        Expense::create([
            'company_id' => CompanyContext::getActiveCompanyId(),
            'project_id' => $validated['project_id'],
            'category_id' => $defaultCategory->id,
            'subcategory_id' => null,
            'expense_type_id' => $defaultExpenseType?->id,
            'subcontractor_id' => $subcontractor->id,
            'item_name' => 'Sub-contractor: ' . $subcontractor->name,
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded and linked to expenses.',
            ]);
        }

        return redirect()->route('admin.subcontractors.show', $subcontractor)
            ->with('success', 'Payment recorded and linked to expenses.');
    }

    protected function paymentValidationError(Request $request, string $field, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }

        return back()->withInput()->withErrors([$field => $message]);
    }
}
