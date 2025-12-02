<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBillModuleRequest;
use App\Http\Requests\UpdateBillModuleRequest;
use App\Http\Requests\ApproveBillRequest;
use App\Models\BillModule;
use App\Models\BillItem;
use App\Models\Measurement;
use App\Models\BillHistory;
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
        $this->middleware('auth:sanctum');
        $this->calculator = $calculator;
    }

    public function index(Request $request, $projectId = null)
    {
        $query = BillModule::with(['project', 'creator', 'aggregate'])
            ->where('company_id', CompanyContext::getActiveCompanyId());

        if ($projectId) {
            $query->where('project_id', $projectId);
        } elseif ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bills = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($bills);
    }

    public function store(StoreBillModuleRequest $request, $projectId = null)
    {
        DB::beginTransaction();
        try {
            $companyId = CompanyContext::getActiveCompanyId();
            $projectId = $projectId ?? $request->project_id;
            
            $companyId = CompanyContext::getActiveCompanyId();
            
            $bill = BillModule::create([
                'company_id' => $companyId,
                'project_id' => $projectId,
                'title' => $request->title,
                'version' => $request->version ?? '1.0',
                'created_by' => auth()->id(),
                'status' => BillModule::STATUS_DRAFT,
                'notes' => $request->notes,
                'mb_number' => $request->mb_number,
                'mb_date' => $request->mb_date,
            ]);

            foreach ($request->items as $index => $itemData) {
                $item = new BillItem($itemData);
                $item->bill_module_id = $bill->id;
                $item->company_id = $companyId;
                $item->sort_order = $index;
                $this->calculator->calculateItem($item);
                $item->save();
            }

            $this->calculator->calculateAggregate(
                $bill->id,
                $request->overhead_percent,
                $request->contingency_percent
            );

            BillHistory::create([
                'bill_module_id' => $bill->id,
                'company_id' => $companyId,
                'action' => 'created',
                'user_id' => auth()->id(),
                'comment' => 'Bill created via API',
            ]);

            DB::commit();

            $bill->load(['items', 'aggregate', 'project']);

            return response()->json($bill, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $bill = BillModule::with(['items', 'aggregate', 'project', 'creator', 'approver', 'history.user'])
            ->findOrFail($id);

        return response()->json($bill);
    }

    public function update(UpdateBillModuleRequest $request, $id)
    {
        $bill = BillModule::findOrFail($id);

        DB::beginTransaction();
        try {
            $bill->update($request->only(['title', 'version', 'notes', 'mb_number', 'mb_date']));

            if ($request->has('items')) {
                $existingIds = collect($request->items)->pluck('id')->filter();
                
                BillItem::where('bill_module_id', $bill->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                foreach ($request->items as $index => $itemData) {
                    if (isset($itemData['id']) && $itemData['id']) {
                        $item = BillItem::find($itemData['id']);
                        $item->fill($itemData);
                    } else {
                        $item = new BillItem($itemData);
                        $item->bill_module_id = $bill->id;
                        $item->company_id = $bill->company_id;
                    }
                    $item->sort_order = $index;
                    $this->calculator->calculateItem($item);
                    $item->save();
                }
            }

            $this->calculator->calculateAggregate(
                $bill->id,
                $request->overhead_percent,
                $request->contingency_percent
            );

            BillHistory::create([
                'bill_module_id' => $bill->id,
                'company_id' => $bill->company_id,
                'action' => 'updated',
                'user_id' => auth()->id(),
                'comment' => 'Bill updated via API',
            ]);

            DB::commit();

            $bill->load(['items', 'aggregate']);

            return response()->json($bill);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approve(ApproveBillRequest $request, $id)
    {
        $bill = BillModule::findOrFail($id);
        
        $action = $request->action === 'approve' ? BillModule::STATUS_APPROVED : BillModule::STATUS_REJECTED;
        
        $bill->update([
            'status' => $action,
            'approved_by' => auth()->id(),
        ]);

        BillHistory::create([
            'bill_module_id' => $bill->id,
            'company_id' => $bill->company_id,
            'action' => $action,
            'user_id' => auth()->id(),
            'comment' => $request->comment ?? "Bill {$action} via API",
        ]);

        return response()->json(['message' => "Bill {$action} successfully", 'bill' => $bill]);
    }

    public function addMeasurement(Request $request, $billId, $itemId)
    {
        $request->validate([
            'measured_quantity' => 'required|numeric|min:0',
            'measure_date' => 'required|date',
            'note' => 'nullable|string',
            'photo_urls' => 'nullable|array',
            'mb_reference' => 'nullable|string|max:100',
        ]);

        $item = BillItem::where('bill_module_id', $billId)->findOrFail($itemId);

        $measurement = Measurement::create([
            'bill_item_id' => $item->id,
            'measured_by' => auth()->id(),
            'measured_quantity' => $request->measured_quantity,
            'measure_date' => $request->measure_date,
            'note' => $request->note,
            'photo_urls' => $request->photo_urls ?? [],
            'mb_reference' => $request->mb_reference,
        ]);

        return response()->json($measurement, 201);
    }

    public function exportExcel($id)
    {
        $bill = BillModule::with(['items', 'aggregate', 'project'])->findOrFail($id);
        
        return Excel::download(new BillModuleExport($bill), "bill_{$id}.xlsx");
    }

    public function exportPdf($id)
    {
        $bill = BillModule::with(['items', 'aggregate', 'project', 'creator', 'approver'])->findOrFail($id);
        
        // Return view for now - can be converted to PDF using dompdf
        return view('admin.bill_modules.pdf', compact('bill'));
    }

    public function history($id)
    {
        $history = BillHistory::with('user')
            ->where('bill_module_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($history);
    }
}
