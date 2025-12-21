<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Staff;
use App\Models\Position;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class StaffController extends Controller
{
    use ValidatesForms;
    
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate staff form data (AJAX endpoint)
     */
    public function validateStaff(Request $request, Staff $staff = null)
    {
        $emailRule = $staff 
            ? 'required|email|unique:staff,email,' . $staff->id
            : 'required|email|unique:staff,email';
        
        $rules = [
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20',
            'position_id' => 'required|exists:positions,id',
            'address' => 'nullable|string',
            'salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Staff::with(['position', 'project'])
            ->where('company_id', $companyId);
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        $staff = $query->latest()->paginate(10)->withQueryString();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        return view('admin.staff.index', compact('staff', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        return view('admin.staff.create', compact('positions', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'position_id' => 'required|exists:positions,id',
            'address' => 'nullable|string',
            'salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['company_id'] = CompanyContext::getActiveCompanyId();

        Staff::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        $staff->load(['position', 'project']);
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        return view('admin.staff.edit', compact('staff', 'positions', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'position_id' => 'required|exists:positions,id',
            'address' => 'nullable|string',
            'salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $staff->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }
}
