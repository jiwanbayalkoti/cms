<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\CompanyContext;
use App\Services\FaviconGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Redirect to index since we're using modals now
        return redirect()->route('admin.companies.index');
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
            'favicon' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company = Company::create($validated);

        // Handle favicon
        $faviconService = app(FaviconGeneratorService::class);
        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $faviconService->generateFromFile($request->file('favicon'), $company->name);
            $company->update(['favicon' => $validated['favicon']]);
        } else {
            // Generate default favicon from first letter
            $validated['favicon'] = $faviconService->generateDefaultFavicon($company->name);
            $company->update(['favicon' => $validated['favicon']]);
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully.',
                'company' => $company
            ]);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        // Redirect to index since we're using modals now
        return redirect()->route('admin.companies.index');
    }

    public function show(Company $company)
    {
        // Get project count for THIS specific company (not filtered by any context)
        // Use direct query to ensure we get the count for the specific company
        $projectCount = \App\Models\Project::where('company_id', $company->id)->count();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            
            return response()->json([
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'address' => $company->address,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'website' => $company->website,
                    'tax_number' => $company->tax_number,
                    'city' => $company->city,
                    'state' => $company->state,
                    'country' => $company->country,
                    'zip' => $company->zip,
                    'logo_url' => $company->getLogoUrl(),
                    'favicon_url' => $company->getFaviconUrl(),
                    'project_count' => $projectCount,
                ]
            ]);
        }
        
        // Redirect to index since we're using modals now
        return redirect()->route('admin.companies.index');
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
            'favicon' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            // delete old logo file if exists
            if ($company->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($validated);

        // Handle favicon
        $faviconService = app(FaviconGeneratorService::class);
        if ($request->hasFile('favicon')) {
            $validated['favicon'] = $faviconService->generateFromFile($request->file('favicon'), $company->name);
            $company->update(['favicon' => $validated['favicon']]);
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully.',
                'company' => $company->fresh()
            ]);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        
        // Return JSON response for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.'
            ]);
        }
        
        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully.');
    }

    public function switch(Request $request)
    {
        // Check if user is super admin
        if (auth()->user()->role !== 'super_admin') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Only super admin can switch companies.'], 403);
            }
            return back()->with('error', 'Only super admin can switch companies.');
        }
        
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        session(['active_company_id' => (int) $request->company_id]);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Active company switched.',
                'company_id' => (int) $request->company_id
            ]);
        }
        
        // If redirect parameter is provided, redirect to that route
        if ($request->has('redirect_to') && $request->redirect_to === 'projects') {
            return redirect()->route('admin.projects.index')->with('success', 'Active company switched.');
        }
        
        return back()->with('success', 'Active company switched.');
    }

    /**
     * Show company profile (accessible to all authenticated users for their own company)
     */
    public function profile()
    {
        try {
        $companyId = CompanyContext::getActiveCompanyId();
        
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

            $company = Company::find($companyId);
            
            if (!$company) {
                return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
            }
        
        return view('admin.companies.profile', compact('company'));
        } catch (\Exception $e) {
            \Log::error('Error loading company profile: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'company_id' => $companyId ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while loading the company profile.');
        }
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
            'favicon' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            // delete old logo file if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        }

        // Handle favicon
        $faviconService = app(FaviconGeneratorService::class);
        if ($request->hasFile('favicon')) {
            // delete old favicon file if exists
            if ($company->favicon && Storage::disk('public')->exists($company->favicon)) {
                Storage::disk('public')->delete($company->favicon);
            }
            $validated['favicon'] = $faviconService->generateFromFile($request->file('favicon'), $company->name);
        } elseif (!$company->favicon) {
            // Generate default favicon if none exists
            $validated['favicon'] = $faviconService->generateDefaultFavicon($company->name);
        }

        $company->update($validated);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Company profile updated successfully.',
                'company' => $company->fresh()
            ]);
        }
        
        return redirect()->route('admin.companies.profile')->with('success', 'Company profile updated successfully.');
    }
}


