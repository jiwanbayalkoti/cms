<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Exports\MeasurementBookExport;
use App\Models\MeasurementBook;
use App\Models\MeasurementBookItem;
use App\Models\Project;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MeasurementBookController extends Controller
{
    use HasProjectAccess;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = MeasurementBook::with(['project', 'creator'])
            ->where('company_id', $companyId);

        $this->filterByAccessibleProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if (!$this->canAccessProject($projectId)) {
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
        }

        $books = $query->latest('measurement_date')->paginate(15)->withQueryString();
        $projects = $this->getAccessibleProjects();

        if ($request->ajax() || $request->wantsJson()) {
            $tbody = view('admin.measurement_books._tbody', compact('books'))->render();
            $pagination = $books->hasPages() ? $books->links()->render() : '';
            return response()->json(['tbody' => $tbody, 'pagination' => $pagination]);
        }

        return view('admin.measurement_books.index', compact('books', 'projects'));
    }

    public function create(Request $request)
    {
        $projects = $this->getAccessibleProjects();
        $dimensionUnits = MeasurementBook::DIMENSION_UNITS;
        return view('admin.measurement_books.create', compact('projects', 'dimensionUnits'));
    }

    public function store(Request $request)
    {
        // Build conditional validation rules
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'contract_no' => 'nullable|string|max:255',
            'measurement_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'dimension_unit' => 'nullable|string|in:ft,m,in,cm',
            'items' => 'required|array|min:1',
            'items.*.works' => 'required|string',
            'items.*.parent_id' => 'nullable|integer',
            'items.*.no' => 'nullable|numeric|min:0',
            'items.*.length_ft' => 'nullable|numeric|min:0',
            'items.*.breadth_ft' => 'nullable|numeric|min:0',
            'items.*.height_ft' => 'nullable|numeric|min:0',
            'items.*.total_qty' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:20',
        ];
        
        // Add conditional quantity validation - required only if length and breadth are provided
        foreach ($request->input('items', []) as $index => $item) {
            $hasLength = !empty($item['length_ft']) && $item['length_ft'] > 0;
            $hasBreadth = !empty($item['breadth_ft']) && $item['breadth_ft'] > 0;
            
            if ($hasLength && $hasBreadth) {
                $rules["items.{$index}.quantity"] = 'required|numeric|min:0';
            } else {
                $rules["items.{$index}.quantity"] = 'nullable|numeric|min:0';
            }
        }
        
        $validated = $request->validate($rules);

        $this->authorizeProjectAccess((int) $validated['project_id']);

        $book = MeasurementBook::create([
            'company_id' => CompanyContext::getActiveCompanyId(),
            'project_id' => $validated['project_id'],
            'contract_no' => $validated['contract_no'] ?? null,
            'measurement_date' => $validated['measurement_date'],
            'title' => $validated['title'] ?? null,
            'dimension_unit' => $validated['dimension_unit'] ?? 'ft',
            'created_by' => auth()->id(),
        ]);

        $createdItems = [];
        $sn = 1;
        
        foreach ($validated['items'] as $i => $it) {
            $parentId = null;
            if (isset($it['parent_id']) && $it['parent_id'] !== '' && $it['parent_id'] !== null) {
                // parent_id is the index in the items array, find the actual created item
                $parentIndex = (int) $it['parent_id'];
                if (isset($createdItems[$parentIndex])) {
                    $parentId = $createdItems[$parentIndex]->id;
                }
            }
            
            // Only assign SN to main works (items without parent)
            $itemSn = $parentId === null ? $sn++ : null;
            
            $item = MeasurementBookItem::create([
                'measurement_book_id' => $book->id,
                'parent_id' => $parentId,
                'sn' => $itemSn,
                'works' => $it['works'],
                'no' => $it['no'] ?? 1,
                'length_ft' => $it['length_ft'] ?? null,
                'length_in' => null,
                'breadth_ft' => $it['breadth_ft'] ?? null,
                'breadth_in' => null,
                'height_ft' => $it['height_ft'] ?? null,
                'height_in' => null,
                'quantity' => $it['quantity'] ?? 0,
                'total_qty' => $it['total_qty'] ?? ($it['quantity'] ?? 0),
                'unit' => $it['unit'] ?? null,
                'sort_order' => $i,
            ]);
            
            $createdItems[$i] = $item;
        }
        
        // Calculate total_qty for main works from their children
        foreach ($createdItems as $item) {
            if ($item->parent_id === null && $item->children()->count() > 0) {
                $totalQty = $item->children()->sum('quantity');
                $item->update(['total_qty' => $totalQty]);
            }
        }

        return redirect()->route('admin.measurement-books.show', $book)
            ->with('success', 'Measurement Book created successfully.');
    }

    public function show(MeasurementBook $measurement_book)
    {
        $this->authorizeProjectAccess((int) $measurement_book->project_id);
        $measurement_book->load(['company', 'project', 'mainItems.children', 'creator']);
        return view('admin.measurement_books.show', compact('measurement_book'));
    }

    public function edit(MeasurementBook $measurement_book)
    {
        $this->authorizeProjectAccess((int) $measurement_book->project_id);
        $measurement_book->load('items');
        // Flatten items for edit form (main items first, then their children)
        $items = [];
        foreach ($measurement_book->mainItems as $mainItem) {
            $items[] = $mainItem;
            foreach ($mainItem->children as $child) {
                $items[] = $child;
            }
        }
        $measurement_book->setRelation('items', collect($items));
        $projects = $this->getAccessibleProjects();
        $dimensionUnits = MeasurementBook::DIMENSION_UNITS;
        return view('admin.measurement_books.edit', compact('measurement_book', 'projects', 'dimensionUnits'));
    }

    public function update(Request $request, MeasurementBook $measurement_book)
    {
        $this->authorizeProjectAccess((int) $measurement_book->project_id);

        // Build conditional validation rules
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'contract_no' => 'nullable|string|max:255',
            'measurement_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'dimension_unit' => 'nullable|string|in:ft,m,in,cm',
            'items' => 'required|array|min:1',
            'items.*.works' => 'required|string',
            'items.*.parent_id' => 'nullable|integer',
            'items.*.no' => 'nullable|numeric|min:0',
            'items.*.length_ft' => 'nullable|numeric|min:0',
            'items.*.breadth_ft' => 'nullable|numeric|min:0',
            'items.*.height_ft' => 'nullable|numeric|min:0',
            'items.*.total_qty' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:20',
        ];
        
        // Add conditional quantity validation - required only if length and breadth are provided
        foreach ($request->input('items', []) as $index => $item) {
            $hasLength = !empty($item['length_ft']) && $item['length_ft'] > 0;
            $hasBreadth = !empty($item['breadth_ft']) && $item['breadth_ft'] > 0;
            
            if ($hasLength && $hasBreadth) {
                $rules["items.{$index}.quantity"] = 'required|numeric|min:0';
            } else {
                $rules["items.{$index}.quantity"] = 'nullable|numeric|min:0';
            }
        }
        
        $validated = $request->validate($rules);

        $measurement_book->update([
            'project_id' => $validated['project_id'],
            'contract_no' => $validated['contract_no'] ?? null,
            'measurement_date' => $validated['measurement_date'],
            'title' => $validated['title'] ?? null,
            'dimension_unit' => $validated['dimension_unit'] ?? 'ft',
        ]);

        $measurement_book->items()->delete();
        $createdItems = [];
        $sn = 1;
        
        foreach ($validated['items'] as $i => $it) {
            $parentId = null;
            if (isset($it['parent_id']) && $it['parent_id'] !== '' && $it['parent_id'] !== null) {
                $parentIndex = (int) $it['parent_id'];
                if (isset($createdItems[$parentIndex])) {
                    $parentId = $createdItems[$parentIndex]->id;
                }
            }
            
            $itemSn = $parentId === null ? $sn++ : null;
            
            $item = MeasurementBookItem::create([
                'measurement_book_id' => $measurement_book->id,
                'parent_id' => $parentId,
                'sn' => $itemSn,
                'works' => $it['works'],
                'no' => $it['no'] ?? 1,
                'length_ft' => $it['length_ft'] ?? null,
                'length_in' => null,
                'breadth_ft' => $it['breadth_ft'] ?? null,
                'breadth_in' => null,
                'height_ft' => $it['height_ft'] ?? null,
                'height_in' => null,
                'quantity' => $it['quantity'] ?? 0,
                'total_qty' => $it['total_qty'] ?? ($it['quantity'] ?? 0),
                'unit' => $it['unit'] ?? null,
                'sort_order' => $i,
            ]);
            
            $createdItems[$i] = $item;
        }
        
        // Calculate total_qty for main works from their children
        foreach ($createdItems as $item) {
            if ($item->parent_id === null && $item->children()->count() > 0) {
                $totalQty = $item->children()->sum('quantity');
                $item->update(['total_qty' => $totalQty]);
            }
        }

        return redirect()->route('admin.measurement-books.show', $measurement_book)
            ->with('success', 'Measurement Book updated successfully.');
    }

    public function destroy(MeasurementBook $measurement_book)
    {
        $this->authorizeProjectAccess((int) $measurement_book->project_id);
        $measurement_book->delete();
        return redirect()->route('admin.measurement-books.index')
            ->with('success', 'Measurement Book deleted successfully.');
    }

    public function exportExcel(MeasurementBook $measurement_book)
    {
        $this->authorizeProjectAccess((int) $measurement_book->project_id);
        $name = 'measurement_book_' . $measurement_book->id . '_' . ($measurement_book->measurement_date ? $measurement_book->measurement_date->format('Y-m-d') : 'export');
        return Excel::download(new MeasurementBookExport($measurement_book), $name . '.xlsx');
    }
}
