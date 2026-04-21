<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyLetterheadAsset;
use App\Models\CompanyLetterheadExport;
use App\Support\CompanyContext;
use App\Services\FaviconGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CompanyController extends Controller
{
    public function __construct()
    {
        // Only restrict full CRUD to super_admin
        // Profile methods will have their own middleware
        $this->middleware(['admin', 'super_admin'])->except([
            'profile',
            'profileUpdate',
            'letterhead',
            'letterheadUpdate',
            'letterheadExportPdf',
            'letterheadAssetStore',
            'letterheadAssetDestroy',
            'letterheadExportList',
        ]);
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
            $fresh = $company->fresh();
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully.',
                'company' => array_merge($fresh->toArray(), [
                    'logo_url' => $fresh->getLogoUrl(),
                    'favicon_url' => $fresh->getFaviconUrl(),
                ]),
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
        // Count projects for this company only. Must bypass CompanyScoped global scope,
        // which filters by session active company — otherwise super admin viewing company B
        // while active company is A would get 0 or a wrong count.
        $projectCount = \App\Models\Project::withoutGlobalScope('company')
            ->where('company_id', $company->id)
            ->count();
        
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
                    'has_stored_logo' => (bool) $company->logo,
                    'has_stored_favicon' => (bool) $company->favicon,
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
            'clear_logo' => 'nullable|boolean',
            'clear_favicon' => 'nullable|boolean',
        ]);

        $clearLogo = $request->boolean('clear_logo');
        $clearFavicon = $request->boolean('clear_favicon');

        unset($validated['clear_logo'], $validated['clear_favicon']);

        $disk = Storage::disk('public');
        $faviconService = app(FaviconGeneratorService::class);

        if ($request->hasFile('logo')) {
            if ($company->logo && $disk->exists($company->logo)) {
                $disk->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        } elseif ($clearLogo) {
            if ($company->logo && $disk->exists($company->logo)) {
                $disk->delete($company->logo);
            }
            $validated['logo'] = null;
        }

        if ($request->hasFile('favicon')) {
            if ($company->favicon && $disk->exists($company->favicon)) {
                $disk->delete($company->favicon);
            }
            $validated['favicon'] = $faviconService->generateFromFile($request->file('favicon'), $company->name);
        } elseif ($clearFavicon) {
            if ($company->favicon && $disk->exists($company->favicon)) {
                $disk->delete($company->favicon);
            }
            $validated['favicon'] = $faviconService->generateDefaultFavicon($company->name);
        }

        $company->update($validated);

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $fresh = $company->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully.',
                'company' => array_merge($fresh->toArray(), [
                    'logo_url' => $fresh->getLogoUrl(),
                    'favicon_url' => $fresh->getFaviconUrl(),
                ]),
            ]);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        [$references, $totalLinkedRows] = $this->companyReferenceSummary($company->id);
        if ($totalLinkedRows > 0) {
            $preview = collect($references)
                ->take(5)
                ->map(function (array $ref) {
                    return $ref['table'] . ' (' . $ref['count'] . ')';
                })
                ->implode(', ');

            $message = 'Cannot delete company: linked data exists in ' . count($references) . ' table(s), total rows ' . $totalLinkedRows . '.';
            if ($preview !== '') {
                $message .= ' Example: ' . $preview . '.';
            }
            $message .= ' Remove or reassign those records first.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'references' => $references,
                ], 422);
            }

            return redirect()->route('admin.companies.index')->with('error', $message);
        }

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

    /**
     * Find every table/column that references companies.id and count matching rows.
     *
     * @return array{0: array<int, array{table: string, column: string, count: int}>, 1: int}
     */
    protected function companyReferenceSummary(int $companyId): array
    {
        $rows = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME = 'companies'
               AND REFERENCED_COLUMN_NAME = 'id'"
        );

        $references = [];
        $totalLinkedRows = 0;

        foreach ($rows as $row) {
            $table = (string) ($row->TABLE_NAME ?? '');
            $column = (string) ($row->COLUMN_NAME ?? '');

            if ($table === '' || $column === '' || $table === 'companies') {
                continue;
            }

            $count = (int) DB::table($table)->where($column, $companyId)->count();
            if ($count < 1) {
                continue;
            }

            $references[] = [
                'table' => $table,
                'column' => $column,
                'count' => $count,
            ];
            $totalLinkedRows += $count;
        }

        usort($references, static fn (array $a, array $b) => $b['count'] <=> $a['count']);

        return [$references, $totalLinkedRows];
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
            'clear_logo' => 'nullable|boolean',
            'clear_favicon' => 'nullable|boolean',
        ]);

        $clearLogo = $request->boolean('clear_logo');
        $clearFavicon = $request->boolean('clear_favicon');

        unset($validated['clear_logo'], $validated['clear_favicon']);

        if ($request->hasFile('logo')) {
            // delete old logo file if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('companies', 'public');
        } elseif ($clearLogo) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = null;
        }

        // Handle favicon
        $faviconService = app(FaviconGeneratorService::class);
        if ($request->hasFile('favicon')) {
            // delete old favicon file if exists
            if ($company->favicon && Storage::disk('public')->exists($company->favicon)) {
                Storage::disk('public')->delete($company->favicon);
            }
            $validated['favicon'] = $faviconService->generateFromFile($request->file('favicon'), $company->name);
        } elseif ($clearFavicon) {
            if ($company->favicon && Storage::disk('public')->exists($company->favicon)) {
                Storage::disk('public')->delete($company->favicon);
            }
            $validated['favicon'] = $faviconService->generateDefaultFavicon($company->name);
        } elseif (!$company->favicon) {
            // Generate default favicon if none exists
            $validated['favicon'] = $faviconService->generateDefaultFavicon($company->name);
        }

        $company->update($validated);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $fresh = $company->fresh();
            return response()->json([
                'success' => true,
                'message' => 'Company profile updated successfully.',
                'company' => array_merge($fresh->toArray(), [
                    'logo_url' => $fresh->getLogoUrl(),
                    'favicon_url' => $fresh->getFaviconUrl(),
                    'has_stored_logo' => (bool) $fresh->logo,
                    'has_stored_favicon' => (bool) $fresh->favicon,
                ]),
            ]);
        }
        
        return redirect()->route('admin.companies.profile')->with('success', 'Company profile updated successfully.');
    }

    public function letterhead()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);
        $company->load('letterheadAssets');

        return view('admin.companies.letterhead', compact('company'));
    }

    public function letterheadExportList()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);
        $letterheadExports = CompanyLetterheadExport::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.companies.letterhead-export-list', compact('company', 'letterheadExports'));
    }

    public function letterheadAssetStore(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $validated = $request->validate([
            'kind' => 'required|in:signature,logo',
            'label' => 'nullable|string|max:120',
            'file' => 'required|file|mimes:jpeg,jpg,png,webp,gif|max:4096',
        ]);

        $path = $request->file('file')->store("company-letterhead-assets/{$companyId}", 'public');

        $maxSort = (int) CompanyLetterheadAsset::where('company_id', $companyId)->max('sort_order');
        $asset = CompanyLetterheadAsset::create([
            'company_id' => $companyId,
            'kind' => $validated['kind'],
            'label' => $validated['label'] ?? null,
            'path' => $path,
            'sort_order' => $maxSort + 1,
        ]);
        $asset->refresh();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Signature or logo image added.',
                'asset' => [
                    'id' => $asset->id,
                    'url' => $asset->getUrl(),
                    'kind' => $asset->kind,
                    'label' => $asset->label,
                ],
                'destroy_url' => route('admin.companies.letterhead.assets.destroy', $asset),
            ]);
        }

        return redirect()->route('admin.companies.letterhead')->with('success', 'Signature or logo image added.');
    }

    public function letterheadAssetDestroy(Request $request, CompanyLetterheadAsset $asset)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId || (int) $asset->company_id !== (int) $companyId) {
            abort(403);
        }

        if ($asset->path && Storage::disk('public')->exists($asset->path)) {
            Storage::disk('public')->delete($asset->path);
        }
        $id = $asset->id;
        $asset->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Image removed.',
                'id' => $id,
            ]);
        }

        return redirect()->route('admin.companies.letterhead')->with('success', 'Image removed.');
    }

    public function letterheadUpdate(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);

        $validated = $request->validate([
            'letterhead_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'letterhead_font_family' => 'required|string|max:191',
            'letterhead_header_alignment' => 'required|in:left,center,right',
            'letterhead_tagline' => 'nullable|string|max:255',
            'letterhead_name_en_size' => 'nullable|numeric|min:1|max:6',
            'letterhead_name_np_size' => 'nullable|numeric|min:1|max:6',
            'letterhead_address_size' => 'nullable|numeric|min:0.8|max:4',
            'letterhead_name_letter_spacing' => 'nullable|numeric|min:-1|max:4',
            'letterhead_name_line_height' => 'nullable|numeric|min:0|max:1.6',
            'letterhead_name_en_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'letterhead_name_np_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'letterhead_address_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'letterhead_name_font_style' => 'nullable|in:normal,italic,oblique',
            'letterhead_name_en_align' => 'nullable|in:left,center,right',
            'letterhead_name_np_align' => 'nullable|in:left,center,right',
            'letterhead_address_align' => 'nullable|in:left,center,right',
            'letterhead_meta_chs_align' => 'nullable|in:left,center,right',
            'letterhead_meta_ps_align' => 'nullable|in:left,center,right',
            'letterhead_meta_date_align' => 'nullable|in:left,center,right',
            'letterhead_meta_date_value' => 'nullable|string|max:100',
            'letterhead_footer_text' => 'nullable|string|max:1000',
            'letterhead_watermark_text' => 'nullable|string|max:60',
            'letterhead_watermark_mode' => 'nullable|in:text,logo',
            'letterhead_show_watermark' => 'nullable|boolean',
            'letterhead_watermark_opacity' => 'nullable|integer|min:1|max:60',
            'letterhead_show_border' => 'nullable|boolean',
            'letterhead_layout_json' => 'nullable|string|max:65535',
        ]);

        $validated['letterhead_template'] = 'red_wave';
        $validated['letterhead_show_watermark'] = $request->boolean('letterhead_show_watermark');
        $validated['letterhead_show_border'] = $request->boolean('letterhead_show_border');
        $validated['letterhead_watermark_opacity'] = (int) ($validated['letterhead_watermark_opacity'] ?? 10);
        $validated['letterhead_watermark_mode'] = in_array(($validated['letterhead_watermark_mode'] ?? 'text'), ['text', 'logo'], true)
            ? $validated['letterhead_watermark_mode']
            : 'text';
        $validated['letterhead_name_en_size'] = isset($validated['letterhead_name_en_size']) ? round((float) $validated['letterhead_name_en_size'], 2) : null;
        $validated['letterhead_name_np_size'] = isset($validated['letterhead_name_np_size']) ? round((float) $validated['letterhead_name_np_size'], 2) : null;
        $validated['letterhead_address_size'] = isset($validated['letterhead_address_size']) ? round((float) $validated['letterhead_address_size'], 2) : null;
        $validated['letterhead_name_letter_spacing'] = isset($validated['letterhead_name_letter_spacing']) ? round((float) $validated['letterhead_name_letter_spacing'], 2) : null;
        $validated['letterhead_name_line_height'] = isset($validated['letterhead_name_line_height']) ? round((float) $validated['letterhead_name_line_height'], 2) : null;
        if (array_key_exists('letterhead_layout_json', $validated)) {
            $rawLayout = $validated['letterhead_layout_json'];
            if ($rawLayout === null || $rawLayout === '') {
                $validated['letterhead_layout_json'] = null;
            } else {
                $decoded = json_decode((string) $rawLayout, true);
                $validated['letterhead_layout_json'] = is_array($decoded) ? json_encode($decoded) : null;
            }
        }

        // मिति only — च.स./प.स. are auto-incremented on PDF export (letterhead_chs_last_no / letterhead_ps_last_no).
        $rawDate = $request->input('letterhead_meta_date_value');
        if ($rawDate === null || $rawDate === '') {
            $validated['letterhead_meta_date_value'] = null;
        } else {
            $s = trim((string) $rawDate);
            $validated['letterhead_meta_date_value'] = $s === '' ? null : mb_substr($s, 0, 100);
        }

        $company->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Letterhead design updated successfully.',
                'company' => $company->fresh(),
            ]);
        }

        return redirect()->route('admin.companies.letterhead')->with('success', 'Letterhead design updated successfully.');
    }

    public function letterheadExportPdf(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if (!$companyId) {
            return redirect()->route('admin.dashboard')->with('error', 'Company not found.');
        }

        $company = Company::findOrFail($companyId);
        try {
        $fontFamilyInput = $request->input('letterhead_font_family');
        if (is_string($fontFamilyInput) && trim($fontFamilyInput) !== '' && mb_strlen($fontFamilyInput) <= 191) {
            $company->letterhead_font_family = trim($fontFamilyInput);
        }
        $headerAlignInput = $request->input('letterhead_header_alignment');
        if (is_string($headerAlignInput) && in_array($headerAlignInput, ['left', 'center', 'right'], true)) {
            $company->letterhead_header_alignment = $headerAlignInput;
        }
        $taglineInput = $request->input('letterhead_tagline');
        if (is_string($taglineInput)) {
            $company->letterhead_tagline = mb_substr($taglineInput, 0, 255);
        }
        $watermarkTextInput = $request->input('letterhead_watermark_text');
        if (is_string($watermarkTextInput)) {
            $company->letterhead_watermark_text = mb_substr($watermarkTextInput, 0, 60);
        }
        $watermarkModeInput = $request->input('letterhead_watermark_mode');
        if (is_string($watermarkModeInput) && in_array($watermarkModeInput, ['text', 'logo'], true)) {
            $company->letterhead_watermark_mode = $watermarkModeInput;
        }
        $company->letterhead_show_watermark = $request->boolean('letterhead_show_watermark');
        $watermarkOpacityInput = $request->input('letterhead_watermark_opacity');
        if ($watermarkOpacityInput !== null && $watermarkOpacityInput !== '') {
            $company->letterhead_watermark_opacity = max(1, min(60, (int) $watermarkOpacityInput));
        }
        $enSizeInput = $request->input('letterhead_name_en_size');
        $npSizeInput = $request->input('letterhead_name_np_size');
        $addressSizeInput = $request->input('letterhead_address_size');
        $letterSpacingInput = $request->input('letterhead_name_letter_spacing');
        $lineHeightInput = $request->input('letterhead_name_line_height');
        if ($enSizeInput !== null && $enSizeInput !== '') {
            $company->letterhead_name_en_size = max(1.0, min(6.0, (float) $enSizeInput));
        }
        if ($npSizeInput !== null && $npSizeInput !== '') {
            $company->letterhead_name_np_size = max(1.0, min(6.0, (float) $npSizeInput));
        }
        if ($addressSizeInput !== null && $addressSizeInput !== '') {
            $company->letterhead_address_size = max(0.8, min(4.0, (float) $addressSizeInput));
        }
        if ($letterSpacingInput !== null && $letterSpacingInput !== '') {
            $company->letterhead_name_letter_spacing = max(-1.0, min(4.0, (float) $letterSpacingInput));
        }
        if ($lineHeightInput !== null && $lineHeightInput !== '') {
            $company->letterhead_name_line_height = max(0, min(1.6, (float) $lineHeightInput));
        }
        $nameEnColorInput = $request->input('letterhead_name_en_color');
        $nameNpColorInput = $request->input('letterhead_name_np_color');
        $addressColorInput = $request->input('letterhead_address_color');
        if (is_string($nameEnColorInput) && preg_match('/^#[0-9A-Fa-f]{6}$/', $nameEnColorInput)) {
            $company->letterhead_name_en_color = $nameEnColorInput;
        }
        if (is_string($nameNpColorInput) && preg_match('/^#[0-9A-Fa-f]{6}$/', $nameNpColorInput)) {
            $company->letterhead_name_np_color = $nameNpColorInput;
        }
        if (is_string($addressColorInput) && preg_match('/^#[0-9A-Fa-f]{6}$/', $addressColorInput)) {
            $company->letterhead_address_color = $addressColorInput;
        }
        $fontStyleInput = $request->input('letterhead_name_font_style');
        if (is_string($fontStyleInput) && in_array($fontStyleInput, ['normal', 'italic', 'oblique'], true)) {
            $company->letterhead_name_font_style = $fontStyleInput;
        }
        foreach ([
            'letterhead_name_en_align',
            'letterhead_name_np_align',
            'letterhead_address_align',
            'letterhead_meta_chs_align',
            'letterhead_meta_ps_align',
            'letterhead_meta_date_align',
        ] as $alignField) {
            $alignValue = $request->input($alignField);
            if (is_string($alignValue) && in_array($alignValue, ['left', 'center', 'right'], true)) {
                $company->{$alignField} = $alignValue;
            }
        }
        // च.स. / प.स.: each export uses last number + 1 (stored in letterhead_*_last_no).
        $company->letterhead_chs_last_no = (int) ($company->letterhead_chs_last_no ?? 0) + 1;
        $company->letterhead_ps_last_no = (int) ($company->letterhead_ps_last_no ?? 0) + 1;
        $company->letterhead_meta_chs_value = (string) $company->letterhead_chs_last_no;
        $company->letterhead_meta_ps_value = (string) $company->letterhead_ps_last_no;
        $metaDateValue = $request->input('letterhead_meta_date_value');
        $company->letterhead_meta_date_value = is_string($metaDateValue) ? mb_substr(trim($metaDateValue), 0, 100) : '';
        $letterContentPages = $this->letterheadBodyPagesFromRequest($request);

        $fontDir = public_path('fonts');
        $tmpDir = storage_path('app/mpdf/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $fontDirs = [base_path('vendor/mpdf/mpdf/ttfonts')];
        if (is_dir($fontDir)) {
            $fontDirs[] = $fontDir;
        }

        $fontVars = new FontVariables();
        $fontDefaults = $fontVars->getDefaults();
        $fontdata = $fontDefaults['fontdata'];
        $hasFontFile = function (string $fontFile) use ($fontDirs): bool {
            foreach ($fontDirs as $dir) {
                if (is_file(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fontFile)) {
                    return true;
                }
            }
            return false;
        };
        $hasNotoSans = $hasFontFile('NotoSans-Regular.ttf') && $hasFontFile('NotoSans-Bold.ttf');
        $hasNotoDevanagari = $hasFontFile('NotoSansDevanagari-Regular.ttf') && $hasFontFile('NotoSansDevanagari-Bold.ttf');
        if ($hasNotoSans) {
            $fontdata['notosans'] = [
                'R' => 'NotoSans-Regular.ttf',
                'B' => 'NotoSans-Bold.ttf',
            ];
        }
        if ($hasNotoDevanagari) {
            $fontdata['notodevanagari'] = [
                'R' => 'NotoSansDevanagari-Regular.ttf',
                'B' => 'NotoSansDevanagari-Bold.ttf',
            ];
        }
        $selectedFont = (string) ($company->letterhead_font_family ?: 'Inter, Arial, sans-serif');
        $pdfDefaultFont = 'freeserif';
        $pdfFontStack = 'freeserif, notosans, notodevanagari, dejavusans, serif';
        $fontMap = [
            'Inter, Arial, sans-serif' => 'notosans',
            'Poppins, Arial, sans-serif' => 'notosans',
            'Roboto, Arial, sans-serif' => 'notosans',
            'Noto Sans, Arial, sans-serif' => 'notosans',
            'Georgia, serif' => 'freeserif',
            'Nirmala UI, Arial, sans-serif' => 'freeserif',
        ];
        if (isset($fontMap[$selectedFont])) {
            $pdfDefaultFont = $fontMap[$selectedFont];
            $pdfFontStack = $pdfDefaultFont . ', notosans, notodevanagari, dejavusans, serif';
        }
        $preferredSubs = [];
        if ($hasNotoSans) {
            $preferredSubs[] = 'notosans';
        }
        if ($hasNotoDevanagari) {
            $preferredSubs[] = 'notodevanagari';
        }
        $backupSubsFont = array_values(array_unique(array_merge(
            $preferredSubs,
            $fontDefaults['backupSubsFont'] ?? []
        )));

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 42,
            'margin_bottom' => 22,
            'margin_header' => 3,
            'margin_footer' => 4,
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'autoMarginPadding' => 2,
            'tempDir' => $tmpDir,
            'fontDir' => $fontDirs,
            'fontdata' => $fontdata,
            'default_font' => $pdfDefaultFont,
            'useSubstitutions' => true,
            'backupSubsFont' => $backupSubsFont,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $headerHtml = view('admin.partials.letterhead-pdf-mpdf-header', [
            'company' => $company,
            'pdfFontStack' => $pdfFontStack,
        ])->render();
        $footerHtml = view('admin.partials.letterhead-pdf-mpdf-footer', ['company' => $company])->render();

        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLHeader($headerHtml, 'O', true);
        $mpdf->SetHTMLHeader($headerHtml, 'E', true);
        $mpdf->SetHTMLFooter($footerHtml);

        if ($company->letterhead_show_watermark) {
            $wOp = max(1, min(60, (int) ($company->letterhead_watermark_opacity ?? 10)));
            $wAlpha = max(0.03, min(0.6, $wOp / 100));
            $wmMode = in_array((string) ($company->letterhead_watermark_mode ?? 'text'), ['text', 'logo'], true)
                ? (string) $company->letterhead_watermark_mode
                : 'text';
            $logoFilePath = null;
            if (!empty($company->logo)) {
                $candidateLogoPath = storage_path('app/public/' . ltrim((string) $company->logo, '/'));
                if (is_file($candidateLogoPath)) {
                    $logoFilePath = $candidateLogoPath;
                }
            }
            if ($wmMode === 'logo' && $logoFilePath) {
                // Keep logo watermark in page-center.
                $mpdf->SetWatermarkImage($logoFilePath, $wAlpha, 'D', 'P');
                $mpdf->showWatermarkImage = true;
            } else {
                $wText = (string) ($company->letterhead_watermark_text ?: $company->name);
                $mpdf->SetWatermarkText($wText);
                $mpdf->showWatermarkText = true;
                $mpdf->watermarkTextAlpha = $wAlpha;
            }
        }

        $bodyHtml = view('admin.companies.letterhead-pdf-body', [
            'company' => $company,
            'letterContentPages' => $letterContentPages,
            'pdfFontStack' => $pdfFontStack,
        ])->render();

        $mpdf->WriteHTML($bodyHtml);

        $overlayJson = trim((string) $request->input('letterhead_overlay_json', ''));
        if ($overlayJson === '') {
            $overlayJson = trim((string) ($company->letterhead_layout_json ?? ''));
        }
        $this->letterheadPdfApplyOverlays($mpdf, $company, $overlayJson);

        $pdfBinary = $mpdf->Output('', Destination::STRING_RETURN);

        $timestamp = now()->format('Ymd_His');
        $safeCompany = Str::slug($company->name ?: 'company');
        $fileName = "letterhead-{$safeCompany}-{$timestamp}.pdf";
        $storagePath = null;
        try {
            $candidatePath = "company-letterheads/{$company->id}/{$fileName}";
            Storage::disk('public')->put($candidatePath, $pdfBinary);
            $storagePath = $candidatePath;
        } catch (\Throwable $e) {
            // If filesystem is read-only/misconfigured on live, still serve the generated PDF.
            report($e);
        }

        // Persist sequence/meta values if DB schema supports them; never fail export on DB mismatch.
        try {
            $company->save();
        } catch (\Throwable $e) {
            report($e);
        }

        // Save export history only if the file was persisted successfully.
        if ($storagePath !== null) {
            try {
                CompanyLetterheadExport::create([
                    'company_id' => $company->id,
                    'path' => $storagePath,
                    'file_name' => $fileName,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
        } catch (\Throwable $e) {
            report($e);

            // Fallback: if mPDF fails in production (font/extension issues), try Dompdf so export still works.
            try {
                $fallbackBodyHtml = view('admin.companies.letterhead-pdf-body', [
                    'company' => $company,
                    'letterContentPages' => $this->letterheadBodyPagesFromRequest($request),
                    'pdfFontStack' => 'DejaVu Sans, sans-serif',
                ])->render();
                $fallbackHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
                    . $fallbackBodyHtml
                    . '</body></html>';

                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($fallbackHtml)->setPaper('a4', 'portrait');
                $fallbackName = 'letterhead-fallback-' . now()->format('Ymd_His') . '.pdf';

                return $pdf->download($fallbackName);
            } catch (\Throwable $fallbackEx) {
                report($fallbackEx);
            }

            $message = 'Letterhead PDF export failed. Please check server PDF configuration. Error: '
                . class_basename($e) . ' - ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->route('admin.companies.letterhead')
                ->with('error', $message);
        }
    }

    /**
     * One sanitized HTML fragment per preview page (explicit mPDF page breaks match browser pagination).
     *
     * @return array<int, string>
     */
    private function letterheadBodyPagesFromRequest(Request $request): array
    {
        $rawJson = (string) $request->input('letter_content_pages_json', '');
        $decoded = json_decode($rawJson, true);
        $pages = [];
        if (is_array($decoded) && $decoded !== []) {
            foreach ($decoded as $chunk) {
                if (!is_string($chunk)) {
                    continue;
                }
                $pages[] = $this->sanitizeLetterheadBodyHtml($chunk);
            }
        }
        if ($pages === []) {
            $pages[] = $this->sanitizeLetterheadBodyHtml((string) $request->input('letter_content', ''));
        }

        return $pages;
    }

    private function sanitizeLetterheadBodyHtml(string $raw): string
    {
        if (mb_strlen($raw) > 50000) {
            $raw = mb_substr($raw, 0, 50000);
        }
        $allowedTags = '<p><br><strong><b><em><i><u><span><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><table><thead><tbody><tr><th><td>';
        $letterContent = strip_tags($raw, $allowedTags);
        // Remove editor-injected inline styles/classes (font-family etc.) so PDF always uses our Devanagari font.
        $letterContent = preg_replace('/\s(?:style|class|lang|dir)="[^"]*"/i', '', $letterContent) ?? $letterContent;
        $letterContent = preg_replace("/\s(?:style|class|lang|dir)='[^']*'/i", '', $letterContent) ?? $letterContent;

        return str_replace(['<span>', '</span>', '&nbsp;'], ['', '', ' '], $letterContent);
    }

    /**
     * Draw signature/logo overlays on existing PDF pages (percent coords match letterhead preview: full A4 210×297 mm).
     */
    private function letterheadPdfApplyOverlays(Mpdf $mpdf, Company $company, string $overlayJson): void
    {
        $overlayJson = trim($overlayJson);
        if ($overlayJson === '' || $overlayJson === '[]') {
            return;
        }

        $items = json_decode($overlayJson, true);
        if (!is_array($items) || $items === []) {
            return;
        }

        $pageCount = is_array($mpdf->pages ?? null) ? count($mpdf->pages) : 0;
        if ($pageCount < 1) {
            return;
        }

        $savedPage = $mpdf->page;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $assetId = (int) ($item['asset_id'] ?? 0);
            if ($assetId < 1) {
                continue;
            }
            $asset = CompanyLetterheadAsset::where('company_id', $company->id)->whereKey($assetId)->first();
            if (!$asset) {
                continue;
            }
            $absPath = $asset->getAbsolutePath();
            if (!$absPath) {
                continue;
            }

            $pageIdx = (int) ($item['page'] ?? 0);
            $pageNum = $pageIdx + 1;
            if ($pageNum < 1 || $pageNum > $pageCount) {
                continue;
            }

            $leftPct = (float) ($item['left'] ?? 0);
            $topPct = (float) ($item['top'] ?? 0);
            $widthPct = (float) ($item['width'] ?? 20);
            $leftPct = max(0, min(100, $leftPct));
            $topPct = max(0, min(100, $topPct));
            $widthPct = max(3, min(100, $widthPct));

            $xMm = ($leftPct / 100) * 210;
            $yMm = ($topPct / 100) * 297;
            $wMm = ($widthPct / 100) * 210;

            $mpdf->page = $pageNum;
            try {
                $mpdf->Image($absPath, $xMm, $yMm, $wMm, 0, '', '', true, false);
            } catch (\Throwable $e) {
                // Skip broken image; avoid failing whole export
            }
        }
        $mpdf->page = $savedPage;
    }
}


