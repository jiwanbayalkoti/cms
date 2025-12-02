<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBillModuleRequest;
use App\Http\Requests\UpdateBillModuleRequest;
use App\Http\Requests\ApproveBillRequest;
use App\Models\BillModule;
use App\Models\BillItem;
use App\Models\BillHistory;
use App\Models\Project;
use App\Models\BillSetting;
use App\Models\BillCategory;
use App\Models\BillSubcategory;
use App\Services\BillCalculatorService;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BillModuleExport;

class BillModuleController extends Controller
{
    protected $calculator;

    public function __construct(BillCalculatorService $calculator)
    {
        $this->middleware('admin');
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $query = BillModule::with(['project', 'creator', 'aggregate'])
            ->where('company_id', CompanyContext::getActiveCompanyId());

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('mb_number', 'like', '%' . $request->search . '%');
            });
        }

        $bills = $query->latest()->paginate(15)->withQueryString();
        $projects = Project::where('company_id', CompanyContext::getActiveCompanyId())
            ->orderBy('name')->get();

        return view('admin.bill_modules.index', compact('bills', 'projects'));
    }

    public function create(Request $request)
    {
        $projectId = $request->get('project_id');
        $projects = Project::where('company_id', CompanyContext::getActiveCompanyId())
            ->orderBy('name')->get();
        
        $billCategories = BillCategory::where('company_id', CompanyContext::getActiveCompanyId())
            ->where('is_active', true)
            ->with('activeSubcategories')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Prepare subcategories data for JavaScript
        $subcategoriesData = [];
        foreach ($billCategories as $cat) {
            $subcategoriesData[$cat->id] = $cat->activeSubcategories->map(function($sub) {
                return ['id' => $sub->id, 'name' => $sub->name];
            })->toArray();
        }

        return view('admin.bill_modules.create', compact('projects', 'projectId', 'billCategories', 'subcategoriesData'));
    }

    public function store(StoreBillModuleRequest $request)
    {
        DB::beginTransaction();
        try {
            $companyId = CompanyContext::getActiveCompanyId();
            
            $bill = BillModule::create([
                'company_id' => $companyId,
                'project_id' => $request->project_id,
                'title' => $request->title,
                'version' => $request->version ?? '1.0',
                'created_by' => auth()->id(),
                'status' => BillModule::STATUS_DRAFT,
                'notes' => $request->notes,
                'mb_number' => $request->mb_number,
                'mb_date' => $request->mb_date,
            ]);

            // Create items
            foreach ($request->items as $index => $itemData) {
                $item = new BillItem();
                $item->bill_module_id = $bill->id;
                $item->bill_category_id = $itemData['bill_category_id'] ?? null;
                $item->bill_subcategory_id = $itemData['bill_subcategory_id'] ?? null;
                $item->description = $itemData['description'];
                $item->uom = $itemData['uom'];
                $item->quantity = $itemData['quantity'];
                $item->unit_rate = $itemData['unit_rate'];
                $item->wastage_percent = $itemData['wastage_percent'] ?? 0;
                $item->tax_percent = $itemData['tax_percent'] ?? 0;
                $item->remarks = $itemData['remarks'] ?? null;
                $item->sort_order = $index;
                
                // Set category/subcategory names from relationships
                if ($item->bill_category_id) {
                    $category = BillCategory::find($item->bill_category_id);
                    $item->category = $category->name ?? '';
                }
                if ($item->bill_subcategory_id) {
                    $subcategory = BillSubcategory::find($item->bill_subcategory_id);
                    $item->subcategory = $subcategory->name ?? '';
                }
                
                $this->calculator->calculateItem($item);
                $item->save();
            }

            // Calculate aggregate
            $this->calculator->calculateAggregate(
                $bill->id,
                $request->overhead_percent,
                $request->contingency_percent
            );

            // Record history
            BillHistory::create([
                'bill_module_id' => $bill->id,
                'company_id' => $companyId,
                'action' => 'created',
                'user_id' => auth()->id(),
                'comment' => 'Bill created',
            ]);

            DB::commit();

            return redirect()->route('admin.bill-modules.show', $bill)
                ->with('success', 'Bill module created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating bill: ' . $e->getMessage());
        }
    }

    public function show(BillModule $bill_module)
    {
        $bill_module->load(['items.billCategory', 'items.billSubcategory', 'aggregate', 'project', 'creator', 'approver', 'history.user']);
        
        return view('admin.bill_modules.show', compact('bill_module'));
    }

    public function edit(BillModule $bill_module)
    {
        if (!$bill_module->canEdit()) {
            return redirect()->route('admin.bill-modules.show', $bill_module)
                ->with('error', 'Cannot edit approved bill. Create a new version instead.');
        }

        $bill_module->load('items');
        $projects = Project::where('company_id', CompanyContext::getActiveCompanyId())
            ->orderBy('name')->get();
        $billCategories = BillCategory::where('company_id', CompanyContext::getActiveCompanyId())
            ->where('is_active', true)
            ->with('activeSubcategories')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Prepare subcategories data for JavaScript
        $subcategoriesData = [];
        foreach ($billCategories as $cat) {
            $subcategoriesData[$cat->id] = $cat->activeSubcategories->map(function($sub) {
                return ['id' => $sub->id, 'name' => $sub->name];
            })->toArray();
        }

        return view('admin.bill_modules.edit', compact('bill_module', 'projects', 'billCategories', 'subcategoriesData'));
    }

    public function update(UpdateBillModuleRequest $request, BillModule $bill_module)
    {
        DB::beginTransaction();
        try {
            $bill_module->update($request->only(['title', 'version', 'notes', 'mb_number', 'mb_date']));

            if ($request->has('items')) {
                // Get existing item IDs
                $existingIds = collect($request->items)->pluck('id')->filter();
                
                // Delete items not in request
                BillItem::where('bill_module_id', $bill_module->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                // Update or create items
                foreach ($request->items as $index => $itemData) {
                    if (isset($itemData['id']) && $itemData['id']) {
                        $item = BillItem::find($itemData['id']);
                    } else {
                        $item = new BillItem();
                        $item->bill_module_id = $bill_module->id;
                        $item->company_id = $bill_module->company_id;
                    }
                    
                    $item->bill_category_id = $itemData['bill_category_id'] ?? null;
                    $item->bill_subcategory_id = $itemData['bill_subcategory_id'] ?? null;
                    $item->description = $itemData['description'];
                    $item->uom = $itemData['uom'];
                    $item->quantity = $itemData['quantity'];
                    $item->unit_rate = $itemData['unit_rate'];
                    $item->wastage_percent = $itemData['wastage_percent'] ?? 0;
                    $item->tax_percent = $itemData['tax_percent'] ?? 0;
                    $item->remarks = $itemData['remarks'] ?? null;
                    $item->sort_order = $index;
                    
                    // Set category/subcategory names from relationships
                    if ($item->bill_category_id) {
                        $category = BillCategory::find($item->bill_category_id);
                        $item->category = $category->name ?? '';
                    }
                    if ($item->bill_subcategory_id) {
                        $subcategory = BillSubcategory::find($item->bill_subcategory_id);
                        $item->subcategory = $subcategory->name ?? '';
                    }
                    
                    $this->calculator->calculateItem($item);
                    $item->save();
                }
            }

            // Recalculate aggregate
            $this->calculator->calculateAggregate(
                $bill_module->id,
                $request->overhead_percent,
                $request->contingency_percent
            );

            // Record history
            BillHistory::create([
                'bill_module_id' => $bill_module->id,
                'company_id' => $bill_module->company_id,
                'action' => 'updated',
                'user_id' => auth()->id(),
                'comment' => 'Bill updated',
            ]);

            DB::commit();

            return redirect()->route('admin.bill-modules.show', $bill_module)
                ->with('success', 'Bill module updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating bill: ' . $e->getMessage());
        }
    }

    public function destroy(BillModule $bill_module)
    {
        if ($bill_module->status === BillModule::STATUS_APPROVED) {
            return back()->with('error', 'Cannot delete approved bill.');
        }

        $bill_module->delete();

        return redirect()->route('admin.bill-modules.index')
            ->with('success', 'Bill module deleted successfully.');
    }

    public function submit(BillModule $bill_module)
    {
        if ($bill_module->status !== BillModule::STATUS_DRAFT) {
            return back()->with('error', 'Only draft bills can be submitted.');
        }

        $bill_module->update(['status' => BillModule::STATUS_SUBMITTED]);

        BillHistory::create([
            'bill_module_id' => $bill_module->id,
            'company_id' => $bill_module->company_id,
            'action' => 'submitted',
            'user_id' => auth()->id(),
            'comment' => 'Bill submitted for approval',
        ]);

        return back()->with('success', 'Bill submitted for approval.');
    }

    public function approve(ApproveBillRequest $request, BillModule $bill_module)
    {
        $action = $request->action === 'approve' ? BillModule::STATUS_APPROVED : BillModule::STATUS_REJECTED;
        
        $bill_module->update([
            'status' => $action,
            'approved_by' => auth()->id(),
        ]);

        BillHistory::create([
            'bill_module_id' => $bill_module->id,
            'company_id' => $bill_module->company_id,
            'action' => $action,
            'user_id' => auth()->id(),
            'comment' => $request->comment ?? ($action === 'approved' ? 'Bill approved' : 'Bill rejected'),
        ]);

        return back()->with('success', "Bill {$action} successfully.");
    }

    public function exportExcel(BillModule $bill_module)
    {
        return Excel::download(new BillModuleExport($bill_module), "bill_{$bill_module->id}.xlsx");
    }

    public function exportPdf(BillModule $bill_module)
    {
        $bill_module->load(['items', 'aggregate', 'project', 'creator', 'approver']);
        
        // For now, return a view. You can use dompdf or similar for PDF generation
        return view('admin.bill_modules.pdf', compact('bill_module'));
    }

    public function report(BillModule $bill_module)
    {
        $bill_module->load(['items', 'aggregate', 'project', 'creator', 'approver']);
        
        return view('admin.bill_modules.report', compact('bill_module'));
    }
}
