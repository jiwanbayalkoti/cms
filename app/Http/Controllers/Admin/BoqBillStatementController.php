<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BoqBillStatementExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\Company;
use App\Models\CompletedWorkRecord;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BoqBillStatementController extends Controller
{
    use HasProjectAccess;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $projects = $this->getAccessibleProjects();

        $projectId = $request->filled('project_id')
            ? (int) $request->project_id
            : ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }

        $query = CompletedWorkRecord::where('company_id', $companyId)
            ->with(['work', 'recordItems.boqItem'])
            ->orderBy('record_date')
            ->orderBy('id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        $records = $query->get();
        // Aggregate by (work_id, boq_item_id): one row per item with sum qty and sum amount
        $rows = [];
        $subtotal = 0;
        foreach ($records->groupBy('boq_work_id') as $workId => $workRecords) {
            $work = $workRecords->first()->work;
            $byItem = [];
            foreach ($workRecords as $record) {
                foreach ($record->recordItems as $ri) {
                    $id = $ri->boq_item_id;
                    $rate = (float) ($ri->boqItem->rate ?? 0);
                    $qty = (float) $ri->completed_qty;
                    if (!isset($byItem[$id])) {
                        $byItem[$id] = [
                            'work' => $work,
                            'boqItem' => $ri->boqItem,
                            'total_qty' => 0,
                            'total_price' => 0,
                        ];
                    }
                    $byItem[$id]['total_qty'] += $qty;
                    $byItem[$id]['total_price'] += $qty * $rate;
                }
            }
            foreach ($byItem as $row) {
                $boqQty = (float) ($row['boqItem']->qty ?? 0);
                $row['remaining_qty'] = max(0, $boqQty - $row['total_qty']);
                $rows[] = $row;
                $subtotal += $row['total_price'];
            }
        }
        $taxPercent = 13;
        $taxAmount = round($subtotal * ($taxPercent / 100), 2);
        $total = $subtotal + $taxAmount;
        $company = Company::find($companyId);
        $billDate = $company->bill_date ? $company->bill_date->format('Y-m-d') : ($records->isNotEmpty() ? $records->max('record_date')->format('Y-m-d') : now()->format('Y-m-d'));
        return view('admin.boq_bill_statements.index', compact('rows', 'company', 'subtotal', 'taxPercent', 'taxAmount', 'total', 'billDate', 'projects', 'projectId'));
    }

    public function exportExcel(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $projectId = $request->filled('project_id') ? (int) $request->project_id : ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }

        $query = CompletedWorkRecord::where('company_id', $companyId)
            ->with(['work', 'recordItems.boqItem'])
            ->orderBy('record_date')
            ->orderBy('id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        $records = $query->get();
        $rows = [];
        $subtotal = 0;
        foreach ($records->groupBy('boq_work_id') as $workId => $workRecords) {
            $work = $workRecords->first()->work;
            $byItem = [];
            foreach ($workRecords as $record) {
                foreach ($record->recordItems as $ri) {
                    $id = $ri->boq_item_id;
                    $rate = (float) ($ri->boqItem->rate ?? 0);
                    $qty = (float) $ri->completed_qty;
                    if (!isset($byItem[$id])) {
                        $byItem[$id] = [
                            'work' => $work,
                            'boqItem' => $ri->boqItem,
                            'total_qty' => 0,
                            'total_price' => 0,
                        ];
                    }
                    $byItem[$id]['total_qty'] += $qty;
                    $byItem[$id]['total_price'] += $qty * $rate;
                }
            }
            foreach ($byItem as $row) {
                $boqQty = (float) ($row['boqItem']->qty ?? 0);
                $row['remaining_qty'] = max(0, $boqQty - $row['total_qty']);
                $rows[] = $row;
                $subtotal += $row['total_price'];
            }
        }
        $taxPercent = 13;
        $taxAmount = round($subtotal * ($taxPercent / 100), 2);
        $total = $subtotal + $taxAmount;
        $company = Company::find($companyId);
        $billDate = $company->bill_date ? $company->bill_date->format('Y-m-d') : ($records->isNotEmpty() ? $records->max('record_date')->format('Y-m-d') : now()->format('Y-m-d'));
        if (empty($rows)) {
            return redirect()->route('admin.boq-bill-statements.index')->with('info', 'No data to export.');
        }
        $filename = 'boq_bill_statement_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new BoqBillStatementExport($company, $rows, $subtotal, $taxPercent, $taxAmount, $total, $billDate), $filename);
    }
}
