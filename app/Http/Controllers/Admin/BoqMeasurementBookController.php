<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BoqMeasurementBookExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\CompletedWorkRecord;
use App\Models\Company;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BoqMeasurementBookController extends Controller
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
        $recordsByWork = $records->groupBy('boq_work_id');
        $workAggregated = [];
        foreach ($recordsByWork as $workId => $workRecords) {
            $work = $workRecords->first()->work;
            $work->load('items');
            $boqItemOrder = $work->items->pluck('id')->toArray();
            $byItem = [];
            foreach ($workRecords as $record) {
                foreach ($record->recordItems as $ri) {
                    $id = $ri->boq_item_id;
                    if (!isset($byItem[$id])) {
                        $byItem[$id] = [
                            'boqItem' => $ri->boqItem,
                            'main_qty' => 0,
                            'main_no' => null,
                            'main_length' => null,
                            'main_breadth' => null,
                            'main_height' => null,
                            'subs' => [],
                        ];
                    }
                    if ($ri->parent_id === null) {
                        $byItem[$id]['main_qty'] += (float) $ri->completed_qty;
                        $byItem[$id]['main_no'] = $ri->no;
                        $byItem[$id]['main_length'] = $ri->length;
                        $byItem[$id]['main_breadth'] = $ri->breadth;
                        $byItem[$id]['main_height'] = $ri->height;
                    } else {
                        $desc = $ri->description ? trim($ri->description) : '—';
                        if (!isset($byItem[$id]['subs'][$desc])) {
                            $byItem[$id]['subs'][$desc] = ['qty' => 0, 'no' => null, 'length' => null, 'breadth' => null, 'height' => null];
                        }
                        $byItem[$id]['subs'][$desc]['qty'] += (float) $ri->completed_qty;
                        $byItem[$id]['subs'][$desc]['no'] = $ri->no;
                        $byItem[$id]['subs'][$desc]['length'] = $ri->length;
                        $byItem[$id]['subs'][$desc]['breadth'] = $ri->breadth;
                        $byItem[$id]['subs'][$desc]['height'] = $ri->height;
                    }
                }
            }
            $rows = [];
            foreach ($boqItemOrder as $bid) {
                if (!isset($byItem[$bid])) {
                    continue;
                }
                $agg = $byItem[$bid];
                $mainQty = $agg['main_qty'];
                $subs = $agg['subs'];
                $subTotal = 0;
                foreach ($subs as $subDesc => $subData) {
                    $subTotal += is_array($subData) ? ($subData['qty'] ?? 0) : (float) $subData;
                }
                $totalQty = $mainQty + $subTotal;
                $hasSubs = count($subs) > 0;
                $rows[] = [
                    'type' => 'main',
                    'boqItem' => $agg['boqItem'],
                    'total_qty' => $totalQty,
                    'quantity' => $hasSubs ? 0 : $totalQty,
                    'has_subs' => $hasSubs,
                    'no' => $agg['main_no'] ?? null,
                    'length' => $agg['main_length'] ?? null,
                    'breadth' => $agg['main_breadth'] ?? null,
                    'height' => $agg['main_height'] ?? null,
                ];
                foreach ($subs as $subDesc => $subData) {
                    $qty = is_array($subData) ? ($subData['qty'] ?? 0) : (float) $subData;
                    $rows[] = [
                        'type' => 'sub',
                        'description' => $subDesc,
                        'total_qty' => $qty,
                        'quantity' => $qty,
                        'unit' => $agg['boqItem']->unit ?? '—',
                        'no' => is_array($subData) ? ($subData['no'] ?? null) : null,
                        'length' => is_array($subData) ? ($subData['length'] ?? null) : null,
                        'breadth' => is_array($subData) ? ($subData['breadth'] ?? null) : null,
                        'height' => is_array($subData) ? ($subData['height'] ?? null) : null,
                    ];
                }
            }
            $dimensionUnit = $workRecords->first()->dimension_unit ?? 'm';
            $workAggregated[] = ['work' => $work, 'rows' => $rows, 'dimension_unit' => $dimensionUnit];
        }
        $company = Company::find($companyId);
        return view('admin.boq_measurement_books.index', compact('workAggregated', 'company', 'projects', 'projectId'));
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
        $workAggregated = [];
        foreach ($records->groupBy('boq_work_id') as $workId => $workRecords) {
            $work = $workRecords->first()->work;
            $work->load('items');
            $boqItemOrder = $work->items->pluck('id')->toArray();
            $byItem = [];
            foreach ($workRecords as $record) {
                foreach ($record->recordItems as $ri) {
                    $id = $ri->boq_item_id;
                    if (!isset($byItem[$id])) {
                        $byItem[$id] = [
                            'boqItem' => $ri->boqItem,
                            'main_qty' => 0,
                            'main_no' => null,
                            'main_length' => null,
                            'main_breadth' => null,
                            'main_height' => null,
                            'subs' => [],
                        ];
                    }
                    if ($ri->parent_id === null) {
                        $byItem[$id]['main_qty'] += (float) $ri->completed_qty;
                        $byItem[$id]['main_no'] = $ri->no;
                        $byItem[$id]['main_length'] = $ri->length;
                        $byItem[$id]['main_breadth'] = $ri->breadth;
                        $byItem[$id]['main_height'] = $ri->height;
                    } else {
                        $desc = $ri->description ? trim($ri->description) : '—';
                        if (!isset($byItem[$id]['subs'][$desc])) {
                            $byItem[$id]['subs'][$desc] = ['qty' => 0, 'no' => null, 'length' => null, 'breadth' => null, 'height' => null];
                        }
                        $byItem[$id]['subs'][$desc]['qty'] += (float) $ri->completed_qty;
                        $byItem[$id]['subs'][$desc]['no'] = $ri->no;
                        $byItem[$id]['subs'][$desc]['length'] = $ri->length;
                        $byItem[$id]['subs'][$desc]['breadth'] = $ri->breadth;
                        $byItem[$id]['subs'][$desc]['height'] = $ri->height;
                    }
                }
            }
            $rows = [];
            foreach ($boqItemOrder as $bid) {
                if (!isset($byItem[$bid])) {
                    continue;
                }
                $agg = $byItem[$bid];
                $mainQty = $agg['main_qty'];
                $subs = $agg['subs'];
                $subTotal = 0;
                foreach ($subs as $subDesc => $subData) {
                    $subTotal += is_array($subData) ? ($subData['qty'] ?? 0) : (float) $subData;
                }
                $totalQty = $mainQty + $subTotal;
                $hasSubs = count($subs) > 0;
                $rows[] = [
                    'type' => 'main',
                    'boqItem' => $agg['boqItem'],
                    'total_qty' => $totalQty,
                    'quantity' => $hasSubs ? 0 : $totalQty,
                    'has_subs' => $hasSubs,
                    'no' => $agg['main_no'] ?? null,
                    'length' => $agg['main_length'] ?? null,
                    'breadth' => $agg['main_breadth'] ?? null,
                    'height' => $agg['main_height'] ?? null,
                ];
                foreach ($subs as $subDesc => $subData) {
                    $qty = is_array($subData) ? ($subData['qty'] ?? 0) : (float) $subData;
                    $rows[] = [
                        'type' => 'sub',
                        'description' => $subDesc,
                        'total_qty' => $qty,
                        'quantity' => $qty,
                        'unit' => $agg['boqItem']->unit ?? '—',
                        'no' => is_array($subData) ? ($subData['no'] ?? null) : null,
                        'length' => is_array($subData) ? ($subData['length'] ?? null) : null,
                        'breadth' => is_array($subData) ? ($subData['breadth'] ?? null) : null,
                        'height' => is_array($subData) ? ($subData['height'] ?? null) : null,
                    ];
                }
            }
            $workAggregated[] = ['work' => $work, 'rows' => $rows];
        }
        $company = Company::find($companyId);
        if (empty($workAggregated)) {
            return redirect()->route('admin.boq-measurement-books.index')->with('info', 'No data to export.');
        }
        $filename = 'boq_measurement_book_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new BoqMeasurementBookExport($company, $workAggregated), $filename);
    }
}
