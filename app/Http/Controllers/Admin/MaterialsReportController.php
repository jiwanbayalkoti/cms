<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\ConstructionMaterial;
use App\Models\MaterialCategory;
use App\Models\Supplier;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConstructionMaterialsExport;

class MaterialsReportController extends Controller
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
        
        // Filters
        $projectId = $request->filled('project_id') ? (int) $request->project_id : ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $category = $request->get('category');
        $supplierId = $request->get('supplier_id');
        $search = $request->get('search');
        $lowStockOnly = $request->boolean('low_stock_only');
        
        // Base query
        $query = ConstructionMaterial::where('company_id', $companyId);
        $this->filterByAccessibleProjects($query, 'project_id');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        if ($startDate) {
            $query->where('delivery_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('delivery_date', '<=', $endDate);
        }
        if ($category) {
            $query->where('material_category', $category);
        }
        if ($supplierId) {
            $supplier = Supplier::find($supplierId);
            if ($supplier) {
                $query->where('supplier_name', $supplier->name);
            }
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('material_name', 'like', '%' . $search . '%')
                  ->orWhere('bill_number', 'like', '%' . $search . '%')
                  ->orWhere('work_type', 'like', '%' . $search . '%');
            });
        }
        
        // Get totals by material
        $totalsQuery = clone $query;
        $totalsByMaterial = $totalsQuery->selectRaw('material_name, MAX(unit) as unit, SUM(quantity_received) as total_received, SUM(quantity_used) as total_used, SUM(quantity_remaining) as total_remaining, SUM(total_cost) as total_cost')
            ->groupBy('material_name')
            ->orderBy('material_name')
            ->get();
        
        // Apply low stock filter
        if ($lowStockOnly) {
            $totalsByMaterial = $totalsByMaterial->filter(function($item) {
                $received = (float) $item->total_received;
                $remaining = (float) $item->total_remaining;
                $usagePercent = $received > 0 ? ($remaining / $received) * 100 : 0;
                return $usagePercent < 20; // Less than 20% remaining
            });
        }
        
        // Get detailed breakdown by project
        $detailsQuery = clone $query;
        $detailsByProject = $detailsQuery->selectRaw('project_id, material_name, MAX(unit) as unit, SUM(quantity_received) as total_received, SUM(quantity_used) as total_used, SUM(quantity_remaining) as total_remaining')
            ->groupBy('project_id', 'material_name')
            ->with('project:id,name')
            ->get()
            ->groupBy('material_name');
        
        // Get categories and suppliers for filters
        $categories = MaterialCategory::where('company_id', $companyId)->orderBy('name')->pluck('name', 'name');
        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id');
        
        // Statistics
        $totalMaterials = $totalsByMaterial->count();
        $totalReceived = $totalsByMaterial->sum('total_received');
        $totalUsed = $totalsByMaterial->sum('total_used');
        $totalRemaining = $totalsByMaterial->sum('total_remaining');
        $totalCost = $totalsByMaterial->sum('total_cost');
        
        // Stock status counts
        $outOfStockCount = $totalsByMaterial->filter(function($item) {
            $remaining = (float) $item->total_remaining;
            return $remaining <= 0;
        })->count();
        
        $lowStockCount = $totalsByMaterial->filter(function($item) {
            $received = (float) $item->total_received;
            $remaining = (float) $item->total_remaining;
            return $received > 0 && $remaining > 0 && ($remaining / $received) * 100 < 20;
        })->count();
        
        $inStockCount = $totalsByMaterial->filter(function($item) {
            $received = (float) $item->total_received;
            $remaining = (float) $item->total_remaining;
            return $received > 0 && ($remaining / $received) * 100 >= 20;
        })->count();
        
        $viewData = compact(
            'totalsByMaterial',
            'detailsByProject',
            'projects',
            'categories',
            'suppliers',
            'projectId',
            'startDate',
            'endDate',
            'category',
            'supplierId',
            'search',
            'lowStockOnly',
            'totalMaterials',
            'totalReceived',
            'totalUsed',
            'totalRemaining',
            'totalCost',
            'lowStockCount',
            'outOfStockCount',
            'inStockCount'
        );
        
        // For AJAX requests, return only the content area
        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.materials_report.index', $viewData)->render();
        }
        
        return view('admin.materials_report.index', $viewData);
    }

    public function exportExcel(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Apply same filters as index
        $projectId = $request->filled('project_id') ? (int) $request->project_id : ProjectContext::getActiveProjectId();
        if ($projectId && !$this->canAccessProject($projectId)) {
            $projectId = null;
        }
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $category = $request->get('category');
        $supplierId = $request->get('supplier_id');
        $search = $request->get('search');
        
        $query = ConstructionMaterial::where('company_id', $companyId);
        $this->filterByAccessibleProjects($query, 'project_id');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($startDate) {
            $query->where('delivery_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('delivery_date', '<=', $endDate);
        }
        if ($category) {
            $query->where('material_category', $category);
        }
        if ($supplierId) {
            $supplier = Supplier::find($supplierId);
            if ($supplier) {
                $query->where('supplier_name', $supplier->name);
            }
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('material_name', 'like', '%' . $search . '%')
                  ->orWhere('bill_number', 'like', '%' . $search . '%')
                  ->orWhere('work_type', 'like', '%' . $search . '%');
            });
        }
        
        $query->orderBy('material_name')->orderBy('delivery_date');
        
        $filename = 'materials_report_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new ConstructionMaterialsExport($query), $filename);
    }
}
