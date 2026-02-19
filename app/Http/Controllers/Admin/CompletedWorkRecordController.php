<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\BoqItem;
use App\Models\BoqWork;
use App\Models\CompletedWorkMaterialUsage;
use App\Models\CompletedWorkRecord;
use App\Models\CompletedWorkRecordItem;
use App\Models\ConstructionMaterial;
use App\Models\Project;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompletedWorkRecordController extends Controller
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

        // Project-wise: use request project_id or default to active project
        $projectId = $request->filled('project_id')
            ? (int) $request->project_id
            : ProjectContext::getActiveProjectId();

        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }

        $query = CompletedWorkRecord::where('company_id', $companyId)
            ->with(['work', 'project', 'recordItems.boqItem', 'materialUsages'])
            ->orderBy('record_date', 'desc')
            ->orderBy('id', 'desc');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $records = $query->paginate(15)->withQueryString();
        
        // Calculate progress for each record (work-wise) using rate-based formula
        // Overall Progress % = SUM (Done Qty × Rate) / SUM (Total Qty × Rate) × 100
        $records->getCollection()->transform(function ($record) use ($projectId) {
            $work = $record->work;
            if (!$work) {
                $record->progress = ['total_boq_qty' => 0, 'total_completed_qty' => 0, 'progress_percent' => 0];
                return $record;
            }
            
            // Get total BOQ amount: SUM (Total Qty × Rate) for this work
            $totalBoqAmount = (float) BoqItem::where('boq_work_id', $work->id)
                ->selectRaw('SUM(qty * rate) as total')
                ->value('total') ?? 0;
            
            // Get completed amount: SUM (Done Qty × Rate) for this work (project-wise if project_id is set)
            $totalCompletedAmount = (float) CompletedWorkRecordItem::query()
                ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
                ->join('boq_items', 'boq_items.id', '=', 'completed_work_record_items.boq_item_id')
                ->where('completed_work_records.boq_work_id', $work->id)
                ->where('completed_work_records.company_id', $record->company_id)
                ->where(function ($q) use ($projectId) {
                    if ($projectId) {
                        $q->where('completed_work_records.project_id', $projectId);
                    } else {
                        $q->whereNull('completed_work_records.project_id');
                    }
                })
                ->selectRaw('SUM(completed_work_record_items.completed_qty * boq_items.rate) as total')
                ->value('total') ?? 0;
            
            // Calculate progress percentage
            $progress = $totalBoqAmount > 0 ? min(100, round(($totalCompletedAmount / $totalBoqAmount) * 100, 1)) : 0;
            
            // Also calculate quantities for display (backward compatibility)
            $totalBoqQty = (float) $work->items->sum('qty');
            $totalCompletedQty = (float) CompletedWorkRecordItem::query()
                ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
                ->where('completed_work_records.boq_work_id', $work->id)
                ->where('completed_work_records.company_id', $record->company_id)
                ->where(function ($q) use ($projectId) {
                    if ($projectId) {
                        $q->where('completed_work_records.project_id', $projectId);
                    } else {
                        $q->whereNull('completed_work_records.project_id');
                    }
                })
                ->sum('completed_work_record_items.completed_qty');
            
            $record->progress = [
                'total_boq_qty' => $totalBoqQty,
                'total_completed_qty' => $totalCompletedQty,
                'total_boq_amount' => $totalBoqAmount,
                'total_completed_amount' => $totalCompletedAmount,
                'progress_percent' => $progress,
            ];
            
            return $record;
        });
        
        return view('admin.completed-work.index', compact('records', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $works = BoqWork::where('company_id', $companyId)->with(['items', 'parent'])->orderByRaw('COALESCE(parent_id, id)')->orderBy('name')->get();
        $selectedWork = null;
        $completedQtyByItem = [];
        $previousSubWorksByBoqItem = [];
        
        // Get project_id for filtering completed work
        $projectId = ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }
        if ($projectId) {
            $project = Project::where('id', $projectId)->where('company_id', $companyId)->first();
            if (!$project) {
                $projectId = null;
            }
        }
        
        if ($request->filled('work_id')) {
            $selectedWork = $works->firstWhere('id', (int) $request->work_id);
            if ($selectedWork && $selectedWork->company_id !== $companyId) {
                $selectedWork = null;
            }
            if ($selectedWork) {
                // Only count completed work for the same project (or NULL project_id if this record doesn't have project_id)
                $completedQtyByItem = CompletedWorkRecordItem::query()
                    ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
                    ->where('completed_work_records.boq_work_id', $selectedWork->id)
                    ->where('completed_work_records.company_id', $companyId)
                    ->where(function ($q) use ($projectId) {
                        if ($projectId) {
                            $q->where('completed_work_records.project_id', $projectId);
                        } else {
                            $q->whereNull('completed_work_records.project_id');
                        }
                    })
                    ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
                    ->groupBy('boq_item_id')
                    ->pluck('total', 'boq_item_id')
                    ->map(fn ($v) => (float) $v)
                    ->toArray();
                $previousSubWorks = CompletedWorkRecordItem::query()
                    ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
                    ->where('completed_work_records.boq_work_id', $selectedWork->id)
                    ->where('completed_work_records.company_id', $companyId)
                    ->whereNotNull('completed_work_record_items.parent_id')
                    ->where(function ($q) use ($projectId) {
                        if ($projectId) {
                            $q->where('completed_work_records.project_id', $projectId);
                        } else {
                            $q->whereNull('completed_work_records.project_id');
                        }
                    })
                    ->select('completed_work_record_items.id', 'completed_work_record_items.boq_item_id', 'completed_work_record_items.description', 'completed_work_record_items.no', 'completed_work_record_items.length', 'completed_work_record_items.breadth', 'completed_work_record_items.height', 'completed_work_record_items.completed_qty')
                    ->orderBy('completed_work_record_items.id', 'desc')
                    ->get();
                $seenKey = [];
                foreach ($previousSubWorks as $s) {
                    $desc = $s->description !== null && trim((string) $s->description) !== '' ? trim((string) $s->description) : '—';
                    $key = $s->boq_item_id . '|' . $desc;
                    if (isset($seenKey[$key])) {
                        continue;
                    }
                    $seenKey[$key] = true;
                    $previousSubWorksByBoqItem[$s->boq_item_id][] = [
                        'description' => $desc,
                        'no' => $s->no,
                        'length' => $s->length,
                        'breadth' => $s->breadth,
                        'height' => $s->height,
                        'completed_qty' => $s->completed_qty,
                    ];
                }
            }
        }
        return view('admin.completed-work.create', compact('works', 'selectedWork', 'completedQtyByItem', 'previousSubWorksByBoqItem'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'boq_work_id' => 'required|exists:boq_works,id',
            'record_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'dimension_unit' => 'nullable|string|in:m,ft,in',
            'items' => 'nullable|array',
            'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
            'items.*.parent_id' => 'nullable|integer|min:0',
            'items.*.description' => 'nullable|string|max:65535',
            'items.*.no' => 'nullable|numeric|min:0',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.breadth' => 'nullable|numeric|min:0',
            'items.*.height' => 'nullable|numeric|min:0',
            'items.*.completed_qty' => 'nullable|numeric|min:0',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $work = BoqWork::findOrFail($data['boq_work_id'])->load('items');
        if ($work->company_id !== $companyId) {
            abort(403);
        }

        // Get project_id for this record (will be set when saving)
        $projectId = ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }
        if ($projectId) {
            $project = Project::where('id', $projectId)->where('company_id', $companyId)->first();
            if (!$project) {
                $projectId = null;
            }
        }

        // Calculate completed qty only for the same project (or NULL project_id if this record doesn't have project_id)
        $completedSoFarByItem = CompletedWorkRecordItem::query()
            ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
            ->where('completed_work_records.boq_work_id', $work->id)
            ->where('completed_work_records.company_id', $companyId)
            ->whereNull('completed_work_record_items.parent_id')
            ->where(function ($q) use ($projectId) {
                if ($projectId) {
                    $q->where('completed_work_records.project_id', $projectId);
                } else {
                    $q->whereNull('completed_work_records.project_id');
                }
            })
            ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        $subQtyByBoqItem = CompletedWorkRecordItem::query()
            ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
            ->where('completed_work_records.boq_work_id', $work->id)
            ->where('completed_work_records.company_id', $companyId)
            ->whereNotNull('completed_work_record_items.parent_id')
            ->where(function ($q) use ($projectId) {
                if ($projectId) {
                    $q->where('completed_work_records.project_id', $projectId);
                } else {
                    $q->whereNull('completed_work_records.project_id');
                }
            })
            ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        foreach ($completedSoFarByItem as $bid => $tot) {
            $completedSoFarByItem[$bid] = $tot + ($subQtyByBoqItem[$bid] ?? 0);
        }

        $workItemsById = $work->items->keyBy('id');
        $workItemIds = $work->items->pluck('id')->toArray();
        $itemsData = $data['items'] ?? [];
        ksort($itemsData, SORT_NUMERIC);
        
        // Build mapping: row index -> boq_item_id
        $indexToBoqItemId = [];
        foreach ($itemsData as $idx => $row) {
            $parentIndex = isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : null;
            
            if ($parentIndex !== null) {
                // Sub-row: get boq_item_id from parent row
                if (!isset($indexToBoqItemId[$parentIndex])) {
                    // Parent not found - skip this row
                    $indexToBoqItemId[$idx] = null;
                    continue;
                }
                $itemId = $indexToBoqItemId[$parentIndex];
            } else {
                // Main row: use boq_item_id from form data
                if (!isset($row['boq_item_id']) || $row['boq_item_id'] === '' || $row['boq_item_id'] === null) {
                    // Main row without boq_item_id - invalid, skip
                    $indexToBoqItemId[$idx] = null;
                    continue;
                }
                $itemId = (int) $row['boq_item_id'];
            }
            
            // Validate itemId belongs to this work
            if (!in_array($itemId, $workItemIds, true)) {
                $indexToBoqItemId[$idx] = null;
                continue;
            }
            
            $indexToBoqItemId[$idx] = $itemId;
        }
        
        // Sum quantities by boq_item_id (main + sub rows)
        $qtyByBoqItem = [];
        $rowDetailsByItem = []; // Track which rows contribute to each item
        foreach ($itemsData as $idx => $row) {
            $boqItemId = $indexToBoqItemId[$idx] ?? null;
            if (!$boqItemId) {
                continue;
            }
            $qty = (float) ($row['completed_qty'] ?? 0);
            // Include ALL rows (even if qty is 0) to track properly
            $qtyByBoqItem[$boqItemId] = ($qtyByBoqItem[$boqItemId] ?? 0) + $qty;
            
            // Track which row contributed this qty (only if qty > 0 for error message)
            if ($qty > 0) {
                if (!isset($rowDetailsByItem[$boqItemId])) {
                    $rowDetailsByItem[$boqItemId] = [];
                }
                $isSubRow = isset($row['parent_id']) && $row['parent_id'] !== '';
                $rowDetailsByItem[$boqItemId][] = [
                    'row_index' => $idx,
                    'qty' => $qty,
                    'is_sub' => $isSubRow,
                    'parent_index' => $isSubRow ? (int) $row['parent_id'] : null,
                    'form_boq_item_id' => isset($row['boq_item_id']) ? (int) $row['boq_item_id'] : null,
                ];
            }
        }
        
        // Validate each item's total qty doesn't exceed remaining
        foreach ($qtyByBoqItem as $itemId => $totalQty) {
            $boqItem = $workItemsById->get($itemId);
            if (!$boqItem) {
                continue;
            }
            
            $boqQty = (float) $boqItem->qty;
            $completedSoFar = $completedSoFarByItem[$itemId] ?? 0;
            $remaining = max(0, $boqQty - $completedSoFar);
            
            if ($totalQty > $remaining) {
                $desc = Str::limit(strip_tags($boqItem->item_description ?? ''), 50) ?: "Item #{$itemId}";
                
                // Build detailed error showing which rows contributed (including main row if it has qty)
                $rowInfo = [];
                foreach ($rowDetailsByItem[$itemId] ?? [] as $detail) {
                    $rowType = $detail['is_sub'] ? 'sub-row' : 'main-row';
                    $parentInfo = '';
                    if ($detail['is_sub']) {
                        $parentInfo = ', parent: row ' . ($detail['parent_index'] + 1);
                        // Also show what boq_item_id the parent row has
                        $parentBoqId = $indexToBoqItemId[$detail['parent_index']] ?? null;
                        if ($parentBoqId) {
                            $parentItem = $workItemsById->get($parentBoqId);
                            $parentDesc = $parentItem ? Str::limit(strip_tags($parentItem->item_description ?? ''), 30) : "Item #{$parentBoqId}";
                            $parentInfo .= " ({$parentDesc})";
                        }
                    }
                    $rowInfo[] = sprintf(
                        'Row %d (%s, qty: %s%s, form_boq_item_id: %s)',
                        $detail['row_index'] + 1,
                        $rowType,
                        number_format($detail['qty'], 4),
                        $parentInfo,
                        $detail['form_boq_item_id'] ?? 'N/A'
                    );
                }
                
                // Also check if main row (row 5 in this case) has qty
                $mainRowIndex = null;
                foreach ($itemsData as $idx => $row) {
                    if (($indexToBoqItemId[$idx] ?? null) === $itemId && !isset($row['parent_id'])) {
                        $mainRowIndex = $idx;
                        $mainQty = (float) ($row['completed_qty'] ?? 0);
                        if ($mainQty > 0 && !in_array($idx, array_column($rowDetailsByItem[$itemId] ?? [], 'row_index'))) {
                            $rowInfo[] = sprintf('Row %d (main-row, qty: %s, form_boq_item_id: %s)', $idx + 1, number_format($mainQty, 4), isset($row['boq_item_id']) ? (int) $row['boq_item_id'] : 'N/A');
                        }
                        break;
                    }
                }
                
                $rowsText = !empty($rowInfo) ? ' (from: ' . implode(', ', $rowInfo) . ')' : '';
                
                $errorMsg = sprintf(
                    'Total completed qty (main + sub works) cannot exceed remaining qty (max %s) for: %s (Item ID: %d, BOQ Qty: %s, Completed So Far: %s, Trying to add: %s)%s',
                    number_format($remaining, 4),
                    $desc,
                    $itemId,
                    number_format($boqQty, 4),
                    number_format($completedSoFar, 4),
                    number_format($totalQty, 4),
                    $rowsText
                );
                return redirect()->back()->withInput()->withErrors(['items' => $errorMsg]);
            }
        }

        $projectId = ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }
        if ($projectId) {
            $project = Project::where('id', $projectId)->where('company_id', $companyId)->first();
            if (!$project) {
                $projectId = null;
            }
        }
        $record = CompletedWorkRecord::create([
            'company_id' => $companyId,
            'project_id' => $projectId,
            'boq_work_id' => $data['boq_work_id'],
            'record_date' => $data['record_date'],
            'notes' => $data['notes'] ?? null,
            'dimension_unit' => $data['dimension_unit'] ?? 'm',
        ]);

        $indexToCreatedId = [];
        foreach ($itemsData as $idx => $row) {
            $parentIndex = isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : null;
            $boqItemId = $indexToBoqItemId[$idx] ?? null;
            if (! $boqItemId) {
                continue;
            }
            $boqItem = $workItemsById->get($boqItemId);
            $boqQty = $boqItem ? (float) $boqItem->qty : 0;
            $completedSoFar = $completedSoFarByItem[$boqItemId] ?? 0;
            $remaining = max(0, $boqQty - $completedSoFar);
            $qty = (float) ($row['completed_qty'] ?? 0);
            $parentId = null;
            if ($parentIndex !== null) {
                $parentId = $indexToCreatedId[$parentIndex] ?? null;
                if (! $parentId) {
                    continue;
                }
            } else {
                if ($qty <= 0) {
                    $hasSubs = false;
                    foreach ($itemsData as $i => $r) {
                        if (isset($r['parent_id']) && (int)$r['parent_id'] === $idx) {
                            $hasSubs = true;
                            break;
                        }
                    }
                    if (! $hasSubs) {
                        continue;
                    }
                }
            }
            $created = $record->recordItems()->create([
                'parent_id' => $parentId,
                'boq_item_id' => $boqItemId,
                'description' => $parentIndex !== null ? ($row['description'] ?? null) : null,
                'no' => isset($row['no']) && $row['no'] !== '' ? (float) $row['no'] : null,
                'length' => isset($row['length']) && $row['length'] !== '' ? (float) $row['length'] : null,
                'breadth' => isset($row['breadth']) && $row['breadth'] !== '' ? (float) $row['breadth'] : null,
                'height' => isset($row['height']) && $row['height'] !== '' ? (float) $row['height'] : null,
                'completed_qty' => min($qty, $parentIndex !== null ? $qty : $remaining),
            ]);
            $indexToCreatedId[$idx] = $created->id;
        }

        if ($record->recordItems()->count() === 0) {
            $record->delete();
            return redirect()->back()->withInput()->withErrors(['items' => 'Enter at least one completed qty.']);
        }

        return redirect()->route('admin.completed-work.index')->with('success', 'Completed work saved.');
    }

    public function edit(CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $completed_work_record->load(['work.items', 'recordItems' => fn ($q) => $q->whereNull('parent_id')->with(['boqItem', 'children'])]);
        $work = $completed_work_record->work;
        $companyId = CompanyContext::getActiveCompanyId();
        $projectId = $completed_work_record->project_id;
        
        // Only count completed work for the same project (or NULL project_id if this record doesn't have project_id)
        $completedQtyByItem = CompletedWorkRecordItem::query()
            ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
            ->where('completed_work_records.boq_work_id', $work->id)
            ->where('completed_work_records.company_id', $companyId)
            ->where(function ($q) use ($projectId) {
                if ($projectId) {
                    $q->where('completed_work_records.project_id', $projectId);
                } else {
                    $q->whereNull('completed_work_records.project_id');
                }
            })
            ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        $thisRecordQtyByItem = CompletedWorkRecordItem::query()
            ->where('completed_work_record_id', $completed_work_record->id)
            ->selectRaw('boq_item_id, SUM(completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        foreach (array_keys($thisRecordQtyByItem) as $bid) {
            $completedQtyByItem[$bid] = ($completedQtyByItem[$bid] ?? 0) - $thisRecordQtyByItem[$bid];
        }
        $mainByBoq = $completed_work_record->recordItems->keyBy('boq_item_id');
        $tableRows = [];
        $rowIndex = 0;
        foreach ($work->items as $boqItem) {
            $mainItem = $mainByBoq->get($boqItem->id);
            $parentIndex = $rowIndex;
            $tableRows[] = ['type' => 'main', 'boq_item' => $boqItem, 'record_item' => $mainItem, 'row_index' => $rowIndex];
            $rowIndex++;
            if ($mainItem) {
                foreach ($mainItem->children as $sub) {
                    $tableRows[] = ['type' => 'sub', 'record_item' => $sub, 'parent_index' => $parentIndex, 'row_index' => $rowIndex];
                    $rowIndex++;
                }
            }
        }
        return view('admin.completed-work.edit', compact('completed_work_record', 'completedQtyByItem', 'tableRows'));
    }

    public function update(Request $request, CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $data = $request->validate([
            'record_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'dimension_unit' => 'nullable|string|in:m,ft,in',
            'items' => 'nullable|array',
            'items.*.boq_item_id' => 'nullable|exists:boq_items,id',
            'items.*.parent_id' => 'nullable|integer|min:0',
            'items.*.description' => 'nullable|string|max:65535',
            'items.*.no' => 'nullable|numeric|min:0',
            'items.*.length' => 'nullable|numeric|min:0',
            'items.*.breadth' => 'nullable|numeric|min:0',
            'items.*.height' => 'nullable|numeric|min:0',
            'items.*.completed_qty' => 'nullable|numeric|min:0',
        ]);
        $projectId = ProjectContext::getActiveProjectId();
        if ($projectId && $this->canAccessProject($projectId)) {
            $project = Project::where('id', $projectId)->where('company_id', $completed_work_record->company_id)->first();
            if ($project) {
                $data['project_id'] = $projectId;
            }
        }
        $completed_work_record->update([
            'record_date' => $data['record_date'],
            'notes' => $data['notes'] ?? null,
            'dimension_unit' => $data['dimension_unit'] ?? $completed_work_record->dimension_unit ?? 'm',
            'project_id' => $data['project_id'] ?? $completed_work_record->project_id,
        ]);
        $work = $completed_work_record->work->load('items');
        $workItemsById = $work->items->keyBy('id');
        $workItemIds = $work->items->pluck('id')->toArray();
        $projectId = $completed_work_record->project_id;
        $companyId = $completed_work_record->company_id;
        
        // Only count completed work for the same project (or NULL project_id if this record doesn't have project_id)
        $completedSoFarByItem = CompletedWorkRecordItem::query()
            ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
            ->where('completed_work_records.boq_work_id', $work->id)
            ->where('completed_work_records.company_id', $companyId)
            ->where('completed_work_records.id', '!=', $completed_work_record->id)
            ->where(function ($q) use ($projectId) {
                if ($projectId) {
                    $q->where('completed_work_records.project_id', $projectId);
                } else {
                    $q->whereNull('completed_work_records.project_id');
                }
            })
            ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        $subQtyByBoqItem = CompletedWorkRecordItem::query()
            ->join('completed_work_records', 'completed_work_records.id', '=', 'completed_work_record_items.completed_work_record_id')
            ->where('completed_work_records.boq_work_id', $work->id)
            ->where('completed_work_records.company_id', $companyId)
            ->where('completed_work_records.id', '!=', $completed_work_record->id)
            ->whereNotNull('completed_work_record_items.parent_id')
            ->where(function ($q) use ($projectId) {
                if ($projectId) {
                    $q->where('completed_work_records.project_id', $projectId);
                } else {
                    $q->whereNull('completed_work_records.project_id');
                }
            })
            ->selectRaw('boq_item_id, SUM(completed_work_record_items.completed_qty) as total')
            ->groupBy('boq_item_id')
            ->pluck('total', 'boq_item_id')
            ->map(fn ($v) => (float) $v)
            ->toArray();
        foreach ($completedSoFarByItem as $bid => $tot) {
            $completedSoFarByItem[$bid] = $tot + ($subQtyByBoqItem[$bid] ?? 0);
        }
        $itemsData = $data['items'] ?? [];
        ksort($itemsData, SORT_NUMERIC);
        $indexToBoqItemId = [];
        foreach ($itemsData as $idx => $row) {
            $parentIndex = isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : null;
            $itemId = $parentIndex !== null ? ($indexToBoqItemId[$parentIndex] ?? null) : (int) ($row['boq_item_id'] ?? 0);
            if ($itemId && ! in_array($itemId, $workItemIds, true)) {
                $itemId = null;
            }
            $indexToBoqItemId[$idx] = $itemId ?: null;
        }
        $qtyByBoqItem = [];
        foreach ($itemsData as $idx => $row) {
            $boqItemId = $indexToBoqItemId[$idx] ?? null;
            if (! $boqItemId) {
                continue;
            }
            $qty = (float) ($row['completed_qty'] ?? 0);
            $qtyByBoqItem[$boqItemId] = ($qtyByBoqItem[$boqItemId] ?? 0) + $qty;
        }
        foreach ($qtyByBoqItem as $itemId => $totalQty) {
            $boqItem = $workItemsById->get($itemId);
            $boqQty = $boqItem ? (float) $boqItem->qty : 0;
            $completedSoFar = $completedSoFarByItem[$itemId] ?? 0;
            $remaining = max(0, $boqQty - $completedSoFar);
            if ($totalQty > $remaining) {
                $desc = $boqItem ? (Str::limit(strip_tags($boqItem->item_description ?? ''), 50) ?: "Item #{$itemId}") : "Item #{$itemId}";
                return redirect()->back()->withInput()->withErrors(['items' => 'Total completed qty (main + sub works) cannot exceed remaining qty (max ' . number_format($remaining, 4) . ') for: ' . $desc]);
            }
        }
        $completed_work_record->recordItems()->delete();
        $indexToCreatedId = [];
        foreach ($itemsData as $idx => $row) {
            $parentIndex = isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : null;
            $boqItemId = $indexToBoqItemId[$idx] ?? null;
            if (! $boqItemId) {
                continue;
            }
            $boqItem = $workItemsById->get($boqItemId);
            $boqQty = $boqItem ? (float) $boqItem->qty : 0;
            $completedSoFar = $completedSoFarByItem[$boqItemId] ?? 0;
            $remaining = max(0, $boqQty - $completedSoFar);
            $qty = (float) ($row['completed_qty'] ?? 0);
            $parentId = null;
            if ($parentIndex !== null) {
                $parentId = $indexToCreatedId[$parentIndex] ?? null;
                if (! $parentId) {
                    continue;
                }
            } else {
                if ($qty <= 0) {
                    $hasSubs = false;
                    foreach ($itemsData as $i => $r) {
                        if (isset($r['parent_id']) && (int)$r['parent_id'] === $idx) {
                            $hasSubs = true;
                            break;
                        }
                    }
                    if (! $hasSubs) {
                        continue;
                    }
                }
            }
            $created = $completed_work_record->recordItems()->create([
                'parent_id' => $parentId,
                'boq_item_id' => $boqItemId,
                'description' => $parentIndex !== null ? ($row['description'] ?? null) : null,
                'no' => isset($row['no']) && $row['no'] !== '' ? (float) $row['no'] : null,
                'length' => isset($row['length']) && $row['length'] !== '' ? (float) $row['length'] : null,
                'breadth' => isset($row['breadth']) && $row['breadth'] !== '' ? (float) $row['breadth'] : null,
                'height' => isset($row['height']) && $row['height'] !== '' ? (float) $row['height'] : null,
                'completed_qty' => min($qty, $parentIndex !== null ? $qty : $remaining),
            ]);
            $indexToCreatedId[$idx] = $created->id;
        }
        return redirect()->route('admin.completed-work.show', $completed_work_record)->with('success', 'Completed work updated.');
    }

    public function show(CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $completed_work_record->load(['work', 'company', 'recordItems' => fn ($q) => $q->whereNull('parent_id')->with(['boqItem', 'children'])]);
        return view('admin.completed-work.show', compact('completed_work_record'));
    }

    public function destroy(CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $completed_work_record->delete();
        return redirect()->route('admin.completed-work.index')->with('success', 'Record deleted.');
    }

    /**
     * JSON: materials list for modal + existing usages grouped by material name (total qty per name).
     */
    public function materialsData(CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $companyId = CompanyContext::getActiveCompanyId();
        $materialsRaw = ConstructionMaterial::where('company_id', $companyId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('material_name')
            ->get(['id', 'material_name', 'unit', 'quantity_remaining', 'quantity_used']);
        $materialOptions = [];
        foreach ($materialsRaw->groupBy('material_name') as $name => $group) {
            $first = $group->sortByDesc('quantity_remaining')->first();
            $totalRemaining = $group->sum('quantity_remaining');
            $materialOptions[] = [
                'id' => $first->id,
                'material_name' => $name,
                'unit' => $first->unit ?? '–',
                'quantity_remaining' => (float) $totalRemaining,
            ];
        }
        usort($materialOptions, fn ($a, $b) => strcasecmp($a['material_name'], $b['material_name']));
        $usages = $completed_work_record->materialUsages()->with('constructionMaterial:id,material_name,unit')->get();
        $byName = [];
        foreach ($usages as $u) {
            $name = $u->constructionMaterial->material_name ?? '–';
            if (! isset($byName[$name])) {
                $byName[$name] = ['material_name' => $name, 'unit' => $u->constructionMaterial->unit ?? '–', 'quantity' => 0, 'usage_ids' => []];
            }
            $byName[$name]['quantity'] += (float) $u->quantity;
            $byName[$name]['usage_ids'][] = $u->id;
        }
        $usagesByMaterial = array_values($byName);
        return response()->json([
            'materials' => $materialOptions,
            'usages' => $usages->map(fn ($u) => [
                'id' => $u->id,
                'construction_material_id' => $u->construction_material_id,
                'material_name' => $u->constructionMaterial->material_name ?? '–',
                'unit' => $u->constructionMaterial->unit ?? '–',
                'quantity' => (float) $u->quantity,
            ]),
            'usagesByMaterial' => $usagesByMaterial,
        ]);
    }

    /**
     * Store used materials for this completed work. Updates construction_materials qty_used / qty_remaining.
     */
    public function storeMaterials(Request $request, CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $request->validate([
            'usages' => 'required|array',
            'usages.*.construction_material_id' => 'required|exists:construction_materials,id',
            'usages.*.quantity' => 'required|numeric|min:0.0001',
        ]);
        $companyId = CompanyContext::getActiveCompanyId();
        foreach ($request->usages as $row) {
            $material = ConstructionMaterial::where('id', $row['construction_material_id'])->where('company_id', $companyId)->first();
            if (! $material) {
                throw ValidationException::withMessages(['usages' => 'Invalid material.']);
            }
            $qty = (float) $row['quantity'];
            $existing = CompletedWorkMaterialUsage::where('completed_work_record_id', $completed_work_record->id)
                ->where('construction_material_id', $material->id)->first();
            $alreadyUsed = $existing ? (float) $existing->quantity : 0;
            $newTotal = $alreadyUsed + $qty;
            $available = (float) $material->quantity_remaining;
            if ($qty > $available) {
                throw ValidationException::withMessages([
                    'usages' => "{$material->material_name}: quantity exceeds remaining ({$available} {$material->unit}).",
                ]);
            }
            if ($existing) {
                $existing->update(['quantity' => $newTotal]);
            } else {
                CompletedWorkMaterialUsage::create([
                    'completed_work_record_id' => $completed_work_record->id,
                    'construction_material_id' => $material->id,
                    'quantity' => $newTotal,
                ]);
            }
            $material->increment('quantity_used', $qty);
            $material->decrement('quantity_remaining', $qty);
        }
        return response()->json(['success' => true, 'message' => 'Materials added.']);
    }

    /**
     * Remove one usage row and return qty to construction material.
     */
    public function destroyMaterialUsage(CompletedWorkRecord $completed_work_record, CompletedWorkMaterialUsage $usage)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        if ($usage->completed_work_record_id !== $completed_work_record->id) {
            abort(404);
        }
        $qty = (float) $usage->quantity;
        $material = $usage->constructionMaterial;
        $usage->delete();
        $material->decrement('quantity_used', $qty);
        $material->increment('quantity_remaining', $qty);
        return response()->json(['success' => true]);
    }

    /**
     * Remove all usages for a material name (by name) and return qty to construction materials.
     */
    public function destroyMaterialUsageByName(Request $request, CompletedWorkRecord $completed_work_record)
    {
        if ($completed_work_record->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $materialName = $request->input('material_name');
        if ($materialName === null || $materialName === '') {
            return response()->json(['success' => false, 'message' => 'material_name required'], 422);
        }
        $usages = $completed_work_record->materialUsages()->whereHas('constructionMaterial', function ($q) use ($materialName) {
            $q->where('material_name', $materialName);
        })->with('constructionMaterial')->get();
        foreach ($usages as $usage) {
            $qty = (float) $usage->quantity;
            $material = $usage->constructionMaterial;
            $usage->delete();
            $material->decrement('quantity_used', $qty);
            $material->increment('quantity_remaining', $qty);
        }
        return response()->json(['success' => true]);
    }
}
