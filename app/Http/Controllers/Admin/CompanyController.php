<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct()
    {
        // Only restrict full CRUD to super_admin
        // Profile methods will have their own middleware
        $this->middleware(['admin', 'super_admin'])->except(['profile', 'profileUpdate']);
    }

    public function index()
    {
        $companies = Company::orderBy('name')->paginate(15);
        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'tax_number' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        Company::create($validated);

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function show(Company $company)
    {
        return view('admin.companies.show', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'tax_number' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            // delete old logo file if exists
            if ($company->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($validated);

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully.');
    }

    public function switch(Request $request)
    {
        if ((int)auth()->user()->company_id !== 1) {
            return back()->with('error', 'Only primary super admin can switch companies.');
        }
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        session(['active_company_id' => (int) $request->company_id]);
        return back()->with('success', 'Active company switched.');
    }

    /**
     * Show company profile (accessible to all authenticated users for their own company)
     */
    public function profile()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);
        
        return view('admin.companies.profile', compact('company'));
    }

    /**
     * Update company profile (accessible to all authenticated users for their own company)
     */
    public function profileUpdate(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'tax_number' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            // delete old logo file if exists
            if ($company->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($validated);

        return redirect()->route('admin.companies.profile')->with('success', 'Company profile updated successfully.');
    }
}


