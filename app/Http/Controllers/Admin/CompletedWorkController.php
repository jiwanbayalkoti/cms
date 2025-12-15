<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompletedWork;
use App\Models\Project;
use App\Models\BillCategory;
use App\Models\BillSubcategory;
use App\Models\BillItem;
use App\Models\BillModule;
use App\Models\BillHistory;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompletedWorkController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = CompletedWork::where('company_id', $companyId)
            ->with(['project', 'billCategory', 'billSubcategory', 'billItem', 'recorder'])
            ->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by work type
        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }
        
        $completedWorks = $query->paginate(15);
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $workTypes = CompletedWork::where('company_id', $companyId)
            ->distinct()
            ->pluck('work_type')
            ->filter()
            ->sort()
            ->values();
        
        return view('admin.completed_works.index', compact('completedWorks', 'projects', 'workTypes'));
    }

    public function create(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $selectedProjectId = $request->get('project_id');
        
        return view('admin.completed_works.create', compact('projects', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $inputMethod = $request->input('quantity_input_method', 'dimensions');
        
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'work_type' => 'required|string|max:255',
            'quantity_input_method' => 'required|in:dimensions,direct',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'uom' => 'required|string|max:50',
            'work_date' => 'required|date',
        ];
        
        // Make dimensions required only if input method is 'dimensions'
        if ($inputMethod === 'dimensions') {
            $rules['length'] = 'required|numeric|min:0';
            $rules['width'] = 'required|numeric|min:0';
            $rules['height'] = 'required|numeric|min:0';
        } else {
            $rules['length'] = 'nullable|numeric|min:0';
            $rules['width'] = 'nullable|numeric|min:0';
            $rules['height'] = 'nullable|numeric|min:0';
        }
        
        $validated = $request->validate($rules);
        
        // Auto-generate description if not provided
        if (empty($validated['description'])) {
            if ($inputMethod === 'dimensions' && isset($validated['length']) && isset($validated['width']) && isset($validated['height'])) {
            $validated['description'] = $validated['work_type'] . ' ' . $validated['length'] . 'm × ' . $validated['width'] . 'm × ' . $validated['height'] . 'm';
            } else {
                $validated['description'] = $validated['work_type'] . ' - Quantity: ' . $validated['quantity'] . ' ' . $validated['uom'];
            }
        }
        
        $validated['company_id'] = $companyId;
        $validated['recorded_by'] = auth()->id();
        $validated['status'] = 'recorded';
        
        CompletedWork::create($validated);
        
        return redirect()->route('admin.completed-works.index')
            ->with('success', 'Completed work record created successfully.');
    }

    public function show(CompletedWork $completed_work)
    {
        $completed_work->load(['project', 'billCategory', 'billSubcategory', 'billItem.billModule', 'recorder']);
        
        return view('admin.completed_works.show', compact('completed_work'));
    }

    public function edit(CompletedWork $completed_work)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        return view('admin.completed_works.edit', compact('completed_work', 'projects'));
    }

    public function update(Request $request, CompletedWork $completed_work)
    {
        $inputMethod = $request->input('quantity_input_method', 'dimensions');
        
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'work_type' => 'required|string|max:255',
            'quantity_input_method' => 'required|in:dimensions,direct',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0',
            'uom' => 'required|string|max:50',
            'work_date' => 'required|date',
        ];
        
        // Make dimensions required only if input method is 'dimensions'
        if ($inputMethod === 'dimensions') {
            $rules['length'] = 'required|numeric|min:0';
            $rules['width'] = 'required|numeric|min:0';
            $rules['height'] = 'required|numeric|min:0';
        } else {
            $rules['length'] = 'nullable|numeric|min:0';
            $rules['width'] = 'nullable|numeric|min:0';
            $rules['height'] = 'nullable|numeric|min:0';
        }
        
        $validated = $request->validate($rules);
        
        // Auto-generate description if not provided
        if (empty($validated['description'])) {
            if ($inputMethod === 'dimensions' && isset($validated['length']) && isset($validated['width']) && isset($validated['height'])) {
            $validated['description'] = $validated['work_type'] . ' ' . $validated['length'] . 'm × ' . $validated['width'] . 'm × ' . $validated['height'] . 'm';
            } else {
                $validated['description'] = $validated['work_type'] . ' - Quantity: ' . $validated['quantity'] . ' ' . $validated['uom'];
            }
        }
        
        $completed_work->update($validated);
        
        return redirect()->route('admin.completed-works.index')
            ->with('success', 'Completed work record updated successfully.');
    }

    public function destroy(CompletedWork $completed_work)
    {
        $completed_work->delete();
        
        return redirect()->route('admin.completed-works.index')
            ->with('success', 'Completed work record deleted successfully.');
    }

    public function generateBillForm(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = CompletedWork::where('company_id', $companyId)
            ->where('status', '!=', 'billed')
            ->with(['project', 'billItem.billModule', 'billCategory', 'billSubcategory']);
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by work type
        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }
        
        $completedWorks = $query->orderBy('work_date', 'desc')->get();
        
        // Group by bill module and project
        $groupedWorks = $completedWorks->groupBy(function ($work) {
            if ($work->billItem && $work->billItem->billModule) {
                return 'bill_' . $work->billItem->billModule->id;
            }
            return 'project_' . $work->project_id;
        });
        
        // Prepare data for JavaScript
        $completedWorksJson = $completedWorks->map(function ($work) {
            $billItemData = null;
            if ($work->billItem) {
                $billItemData = [
                    'unit_rate' => (float) $work->billItem->unit_rate,
                    'wastage_percent' => (float) $work->billItem->wastage_percent,
                    'tax_percent' => (float) $work->billItem->tax_percent,
                    'category' => $work->billItem->category ?? '',
                    'subcategory' => $work->billItem->subcategory ?? '',
                ];
            }
            
            $categoryName = '';
            if ($work->billCategory) {
                $categoryName = $work->billCategory->name;
            } else {
                $categoryName = $work->work_type ?? '';
            }
            
            $subcategoryName = null;
            if ($work->billSubcategory) {
                $subcategoryName = $work->billSubcategory->name;
            }
            
            return [
                'id' => $work->id,
                'work_type' => $work->work_type,
                'description' => $work->description,
                'quantity' => (float) $work->quantity,
                'uom' => $work->uom,
                'bill_item' => $billItemData,
                'category' => $categoryName,
                'subcategory' => $subcategoryName,
                'remarks' => $work->remarks ?? '',
            ];
        })->values();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        return view('admin.completed_works.generate_bill', compact('completedWorks', 'groupedWorks', 'projects', 'completedWorksJson'));
    }

    public function generateBill(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'mb_number' => 'nullable|string|max:255',
            'mb_date' => 'nullable|date',
            'overhead_percent' => 'nullable|numeric|min:0|max:100',
            'contingency_percent' => 'nullable|numeric|min:0|max:100',
            'completed_work_ids' => 'required|array|min:1',
            'completed_work_ids.*' => 'exists:completed_works,id',
            'items' => 'required|array|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            // Create new bill module
            $bill = BillModule::create([
                'company_id' => $companyId,
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'version' => $validated['version'] ?? '1.0',
                'created_by' => auth()->id(),
                'status' => BillModule::STATUS_DRAFT,
                'mb_number' => $validated['mb_number'] ?? null,
                'mb_date' => $validated['mb_date'] ?? null,
                'notes' => 'Generated from completed works',
            ]);
            
            $calculator = app(\App\Services\BillCalculatorService::class);
            
            // Create bill items from completed works and direct works
            $sortOrder = 0;
            foreach ($validated['items'] as $itemData) {
                $completedWork = null;
                
                // Check if this is a direct work (not from completed works)
                if (isset($itemData['direct_work']) && $itemData['direct_work']) {
                    // Create completed work record first for direct works
                    $completedWork = CompletedWork::create([
                        'company_id' => $companyId,
                        'project_id' => $validated['project_id'],
                        'work_type' => $itemData['work_type'],
                        'length' => $itemData['length'],
                        'width' => $itemData['width'],
                        'height' => $itemData['height'],
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'uom' => $itemData['uom'] ?? 'm³',
                        'work_date' => now(),
                        'recorded_by' => auth()->id(),
                        'status' => 'billed', // Mark as billed immediately
                    ]);
                } elseif (isset($itemData['completed_work_id'])) {
                    $completedWork = CompletedWork::find($itemData['completed_work_id']);
                    if (!$completedWork) continue;
                } else {
                    continue; // Skip if neither direct work nor completed work
                }
                
                $billItem = new BillItem();
                $billItem->bill_module_id = $bill->id;
                $billItem->company_id = $companyId;
                
                // If linked to original bill item, copy its details
                if ($completedWork->billItem) {
                    $originalItem = $completedWork->billItem;
                    $billItem->bill_category_id = $originalItem->bill_category_id;
                    $billItem->bill_subcategory_id = $originalItem->bill_subcategory_id;
                    $billItem->category = $originalItem->category;
                    $billItem->subcategory = $originalItem->subcategory;
                    $billItem->unit_rate = $itemData['unit_rate'] ?? $originalItem->unit_rate;
                    $billItem->wastage_percent = $itemData['wastage_percent'] ?? $originalItem->wastage_percent;
                    $billItem->tax_percent = $itemData['tax_percent'] ?? $originalItem->tax_percent;
                } else {
                    $billItem->bill_category_id = $completedWork->bill_category_id;
                    $billItem->bill_subcategory_id = $completedWork->bill_subcategory_id;
                    $billItem->category = $completedWork->billCategory ? $completedWork->billCategory->name : $completedWork->work_type;
                    $billItem->subcategory = $completedWork->billSubcategory ? $completedWork->billSubcategory->name : null;
                    $billItem->unit_rate = $itemData['unit_rate'] ?? 0;
                    $billItem->wastage_percent = $itemData['wastage_percent'] ?? 0;
                    $billItem->tax_percent = $itemData['tax_percent'] ?? 0;
                }
                
                // Use quantity from completed work
                $billItem->description = $itemData['description'] ?? $completedWork->description;
                $billItem->uom = $completedWork->uom;
                $billItem->quantity = $itemData['quantity'] ?? $completedWork->quantity;
                $billItem->remarks = $completedWork->remarks ?? $itemData['remarks'] ?? null;
                $billItem->sort_order = $sortOrder++;
                
                $calculator->calculateItem($billItem);
                $billItem->save();
                
                // Mark completed work as billed
                $completedWork->update(['status' => 'billed']);
            }
            
            // Calculate aggregate
            $calculator->calculateAggregate(
                $bill->id,
                $validated['overhead_percent'] ?? null,
                $validated['contingency_percent'] ?? null
            );
            
            // Record history
            BillHistory::create([
                'bill_module_id' => $bill->id,
                'company_id' => $companyId,
                'action' => 'created',
                'user_id' => auth()->id(),
                'comment' => 'Bill generated from completed works',
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.bill-modules.show', $bill)
                ->with('success', 'Bill generated successfully from completed works. You can edit it before submitting.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error generating bill: ' . $e->getMessage());
        }
    }
}
