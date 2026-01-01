<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Company;
use App\Models\Project;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    use ValidatesForms;
    
    public function __construct()
    {
        $this->middleware('admin');
        // Exclude approval methods from admin middleware redirects - handle in method itself
        $this->middleware(function ($request, $next) {
            // For approval routes, ensure JSON response
            if ($request->is('*/gallery/*/approve') || $request->is('*/gallery/*/disapprove') || $request->is('*/gallery/*/bulk-approve')) {
                $request->headers->set('Accept', 'application/json');
                $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            }
            return $next($request);
        });
    }
    
    /**
     * Validate project form data (AJAX endpoint)
     */
    public function validateProjectForm(Request $request)
    {
        $statusRule = implode(',', Project::statusOptions());
        
        $rules = [
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . $statusRule,
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
        
        return $this->validateForm($request, $rules);
    }

    public function index()
    {
        $user = auth()->user();
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = Project::with('company');
        
        // For regular admins, always filter by their company_id
        // For super admins, show all companies or the selected company
        if ($user->role !== 'super_admin') {
            // Regular admin - only show their company's projects
            if ($user->company_id) {
                $query->where('company_id', $user->company_id);
                // Apply project access restrictions
                // If user has specific project assignments, filter by them
                // If user has no assignments (null), they see all projects in their company
                $accessibleProjectIds = $user->getAccessibleProjectIds();
                if ($accessibleProjectIds !== null && !empty($accessibleProjectIds)) {
                    // User has specific project assignments - filter to only those
                    $query->whereIn('id', $accessibleProjectIds);
                }
                // If $accessibleProjectIds is null, user has access to all projects in their company (no filter needed)
            } else {
                // User has no company, return empty
                return view('admin.projects.index', [
                    'companiesWithProjects' => collect(),
                ]);
            }
        } else {
            // Super admin - filter by active company if set (and not company 1)
            if ($companyId && (int) $companyId !== 1) {
                $query->where('company_id', $companyId);
            }
            // If companyId is 1 or null, show all companies
        }
        
        // Get all projects and group by company
        $allProjects = $query->latest('updated_at')->get();
        
        // Group projects by company
        $projectsByCompany = $allProjects->groupBy('company_id');
        
        // Eager load all companies at once to avoid N+1 queries
        $companyIds = $projectsByCompany->keys()->filter()->toArray();
        $companies = Company::whereIn('id', $companyIds)->get()->keyBy('id');
        
        // Get companies with their projects
        $companiesWithProjects = collect();
        foreach ($projectsByCompany as $compId => $projects) {
            $company = $compId ? ($companies[$compId] ?? null) : null;
            $companiesWithProjects->push([
                'company' => $company,
                'company_id' => $compId,
                'company_name' => $company ? $company->name : 'No Company',
                'projects' => $projects
            ]);
        }

        return view('admin.projects.index', [
            'companiesWithProjects' => $companiesWithProjects,
        ]);
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can create projects.');
        }
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'statuses' => Project::statusOptions(),
            ]);
        }
        
        return view('admin.projects.create', [
            'statuses' => Project::statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can create projects.');
        }
        
        $validated = $this->validateProject($request);
        
        // Get company_id and validate it exists
        $companyId = CompanyContext::getActiveCompanyId() ?? auth()->user()->company_id;
        
        // Validate company exists
        if ($companyId && !Company::find($companyId)) {
            return back()
                ->withInput()
                ->with('error', 'Invalid company selected. Please select a valid company.');
        }
        
        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();

        // Handle file uploads
        $files = $this->handleFileUploads($request);
        if ($files) {
            $validated['files'] = $files;
        }

        // Handle photo gallery uploads
        $photos = $this->handlePhotoUploads($request);
        if ($photos) {
            $validated['photos'] = $photos;
        }

        try {
            $project = Project::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                $errorMessage = 'Cannot create project: Invalid company selected. Please ensure you have selected a valid company.';
                
                // Return JSON response for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['company_id' => [$errorMessage]]
                    ], 422);
                }
                
                return back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
            throw $e;
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $project->load('company');
            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'budget' => $project->budget,
                    'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : null,
                    'company_id' => $project->company_id,
                    'company' => $project->company ? [
                        'id' => $project->company->id,
                        'name' => $project->company->name,
                    ] : null,
                    'files' => $project->files ?? [],
                ]
            ]);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        // Site engineers cannot access project details, only galleries
        if (auth()->user()->role === 'site_engineer') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'You only have access to project galleries.'], 403);
            }
            abort(403, 'You only have access to project galleries.');
        }
        
        $authorizationResult = $this->authorizeCompanyAccess($project);
        if ($authorizationResult) {
            return $authorizationResult;
        }

        $project->load(['company', 'creator', 'updater']);
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            $statusColors = [
                'planned' => 'bg-gray-100 text-gray-800',
                'active' => 'bg-green-100 text-green-800',
                'on_hold' => 'bg-yellow-100 text-yellow-800',
                'completed' => 'bg-blue-100 text-blue-800',
                'cancelled' => 'bg-red-100 text-red-800',
            ];
            
            $activeCompanyId = \App\Support\CompanyContext::getActiveCompanyId();
            
            // Format files with URLs
            $files = [];
            if ($project->files && is_array($project->files)) {
                foreach ($project->files as $file) {
                    $files[] = [
                        'name' => $file['name'] ?? 'Document',
                        'original_name' => $file['original_name'] ?? '',
                        'size' => $file['size'] ?? 0,
                        'path' => $file['path'] ?? '',
                        'url' => \App\Helpers\StorageHelper::url($file['path'] ?? ''),
                    ];
                }
            }
            
            return response()->json([
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'status_color' => $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800',
                    'status_text' => \Str::headline($project->status),
                    'budget' => $project->budget,
                    'budget_formatted' => $project->budget ? number_format($project->budget, 2) : 'Not set',
                    'start_date' => $project->start_date ? $project->start_date->format('M d, Y') : 'TBD',
                    'end_date' => $project->end_date ? $project->end_date->format('M d, Y') : 'TBD',
                    'timeline' => ($project->start_date ? $project->start_date->format('M d, Y') : 'TBD') . ' — ' . ($project->end_date ? $project->end_date->format('M d, Y') : 'TBD'),
                    'company_id' => $project->company_id,
                    'company_name' => optional($project->company)->name ?? '—',
                    'files' => $files,
                    'created_by' => $project->creator->name ?? 'System',
                    'created_at' => $project->created_at ? $project->created_at->format('M d, Y H:i') : '',
                    'updated_by' => $project->updater->name ?? $project->creator->name ?? 'System',
                    'updated_at' => $project->updated_at ? $project->updated_at->format('M d, Y H:i') : '',
                    'show_company' => (int) $activeCompanyId === 1,
                ],
            ]);
        }

        // Redirect to index since we're using modals now
        return redirect()->route('admin.projects.index');
    }

    public function gallery(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        $project->load(['company']);
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            // Render gallery content without layout for modal
            $galleryContent = view('admin.projects.gallery-content', [
                'project' => $project,
            ])->render();
            
            return response()->json([
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'company' => $project->company ? [
                        'id' => $project->company->id,
                        'name' => $project->company->name,
                    ] : null,
                ],
                'photos' => $project->photos ?? [],
                'html' => $galleryContent,
            ]);
        }

        return view('admin.projects.gallery', [
            'project' => $project,
        ]);
    }

    public function addAlbum(Request $request, Project $project)
    {
        $this->authorizeCompanyAccess($project);
        
        // Allow all authenticated users to add albums

        $request->validate([
            'name' => 'required|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $photos = $project->photos ?? [];
        $newAlbum = [
            'name' => $request->input('name'),
            'photos' => [],
        ];

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo && $photo->isValid()) {
                    $compressedPath = $this->compressAndStoreImage($photo, 'projects/photos');
                    if ($compressedPath) {
                        $newAlbum['photos'][] = [
                            'path' => $compressedPath,
                            'original_name' => $photo->getClientOriginalName(),
                            'size' => \Storage::disk('public')->size($compressedPath),
                            'mime_type' => 'image/jpeg',
                            'uploaded_at' => now()->toDateTimeString(),
                            'approval_status' => 'pending', // Default status
                            'approved_by' => null,
                            'approved_at' => null,
                        ];
                    }
                }
            }
        }

        $photos[] = $newAlbum;
        $project->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Album added successfully.',
            'album' => $newAlbum,
            'albumIndex' => count($photos) - 1,
        ]);
    }

    public function updateAlbum(Request $request, Project $project, int $albumIndex)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only administrators can update albums.'], 403);
        }

        $photos = $project->photos ?? [];
        if (!isset($photos[$albumIndex])) {
            return response()->json(['error' => 'Album not found.'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'delete_photos' => 'nullable|array',
        ]);

        $album = $photos[$albumIndex];

        // Update album name
        if ($request->filled('name')) {
            $album['name'] = trim($request->input('name'));
            Log::info('Updating album name', [
                'project_id' => $project->id,
                'album_index' => $albumIndex,
                'old_name' => $photos[$albumIndex]['name'] ?? 'N/A',
                'new_name' => $album['name']
            ]);
        }

        // Delete photos
        if ($request->has('delete_photos')) {
            foreach ($request->input('delete_photos') as $photoIndex) {
                $photoIndex = (int) $photoIndex;
                if (isset($album['photos'][$photoIndex])) {
                    $photoPath = $album['photos'][$photoIndex]['path'] ?? null;
                    if ($photoPath) {
                        $this->deletePhotoFile($photoPath);
                    }
                    unset($album['photos'][$photoIndex]);
                }
            }
            $album['photos'] = array_values($album['photos']);
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo && $photo->isValid()) {
                    $compressedPath = $this->compressAndStoreImage($photo, 'projects/photos');
                    if ($compressedPath) {
                        $album['photos'][] = [
                            'path' => $compressedPath,
                            'original_name' => $photo->getClientOriginalName(),
                            'size' => \Storage::disk('public')->size($compressedPath),
                            'mime_type' => 'image/jpeg',
                            'uploaded_at' => now()->toDateTimeString(),
                            'approval_status' => 'pending', // Default status
                            'approved_by' => null,
                            'approved_at' => null,
                        ];
                    }
                }
            }
        }

        $photos[$albumIndex] = $album;
        
        // Save to database
        $project->photos = $photos;
        $saved = $project->save();
        
        // Refresh to ensure data is persisted
        $project->refresh();
        
        Log::info('Album update saved', [
            'project_id' => $project->id,
            'album_index' => $albumIndex,
            'saved' => $saved,
            'album_name' => $album['name'] ?? 'N/A',
            'photos_count' => count($project->photos ?? [])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Album updated successfully.',
            'album' => $album,
        ]);
    }

    public function deleteAlbum(Project $project, int $albumIndex)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only administrators can delete albums.'], 403);
        }

        $photos = $project->photos ?? [];
        if (!isset($photos[$albumIndex])) {
            return response()->json(['error' => 'Album not found.'], 404);
        }

        // Delete all photos in the album
        $album = $photos[$albumIndex];
        if (isset($album['photos']) && is_array($album['photos'])) {
            foreach ($album['photos'] as $photo) {
                $photoPath = $photo['path'] ?? null;
                if ($photoPath) {
                    $this->deletePhotoFile($photoPath);
                }
            }
        }

        unset($photos[$albumIndex]);
        $photos = array_values($photos); // Reindex array
        $project->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Album deleted successfully.',
        ]);
    }

    public function addPhotos(Request $request, Project $project, int $albumIndex)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only administrators can add photos.'], 403);
        }

        $photos = $project->photos ?? [];
        if (!isset($photos[$albumIndex])) {
            return response()->json(['error' => 'Album not found.'], 404);
        }

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $album = $photos[$albumIndex];
        $newPhotos = [];

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo && $photo->isValid()) {
                    $compressedPath = $this->compressAndStoreImage($photo, 'projects/photos');
                    if ($compressedPath) {
                        $newPhoto = [
                            'path' => $compressedPath,
                            'original_name' => $photo->getClientOriginalName(),
                            'size' => \Storage::disk('public')->size($compressedPath),
                            'mime_type' => 'image/jpeg',
                            'uploaded_at' => now()->toDateTimeString(),
                            'approval_status' => 'pending', // Default status
                            'approved_by' => null,
                            'approved_at' => null,
                        ];
                        $album['photos'][] = $newPhoto;
                        $newPhotos[] = $newPhoto;
                    }
                }
            }
        }

        $photos[$albumIndex] = $album;
        $project->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Photos added successfully.',
            'photos' => $newPhotos,
        ]);
    }

    public function deletePhoto(Project $project, int $albumIndex, int $photoIndex)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only administrators can delete photos.'], 403);
        }

        $photos = $project->photos ?? [];
        if (!isset($photos[$albumIndex])) {
            return response()->json(['error' => 'Album not found.'], 404);
        }

        $album = $photos[$albumIndex];
        if (!isset($album['photos'][$photoIndex])) {
            return response()->json(['error' => 'Photo not found.'], 404);
        }

        // Delete photo file
        $photoPath = $album['photos'][$photoIndex]['path'] ?? null;
        if ($photoPath) {
            $this->deletePhotoFile($photoPath);
        }

        unset($album['photos'][$photoIndex]);
        $album['photos'] = array_values($album['photos']); // Reindex array
        $photos[$albumIndex] = $album;
        $project->update(['photos' => $photos]);

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully.',
        ]);
    }

    public function approvePhoto(Request $request, $projectId, int $albumIndex, int $photoIndex)
    {
        // Log immediately to see if method is called
        Log::info('=== approvePhoto METHOD CALLED ===', [
            'project_id_param' => $projectId,
            'album_index' => $albumIndex,
            'photo_index' => $photoIndex,
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'all_params' => $request->all(),
        ]);
        
        // Get project manually to avoid route model binding issues
        $project = Project::find($projectId);
        if (!$project) {
            Log::error('Project not found', ['project_id' => $projectId]);
            return response()->json(['error' => 'Project not found.'], 404);
        }
        
        // Log the request for debugging
        Log::info('approvePhoto processing', [
            'project_id' => $project->id,
            'album_index' => $albumIndex,
            'photo_index' => $photoIndex,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? null,
            'is_ajax' => request()->ajax(),
            'expects_json' => request()->expectsJson(),
            'accept_header' => request()->header('Accept'),
        ]);
        
        // Force JSON response for AJAX requests
        request()->headers->set('Accept', 'application/json');
        request()->headers->set('X-Requested-With', 'XMLHttpRequest');
        
        try {
            // Check authorization - if it returns a response, return it
            $authResponse = $this->authorizeCompanyAccess($project);
            if ($authResponse) {
                return $authResponse;
            }
            
            // Only site engineers can approve photos
            if (auth()->user()->role !== 'site_engineer') {
                return response()->json(['error' => 'Only site engineers can approve photos.'], 403);
            }

            $photos = $project->photos ?? [];
            if (!is_array($photos) || !isset($photos[$albumIndex])) {
                return response()->json(['error' => 'Album not found.'], 404);
            }

            $album = $photos[$albumIndex];
            if (!isset($album['photos']) || !is_array($album['photos']) || !isset($album['photos'][$photoIndex])) {
                return response()->json(['error' => 'Photo not found.'], 404);
            }

            // Update approval status
            $album['photos'][$photoIndex]['approval_status'] = 'approved';
            $album['photos'][$photoIndex]['approved_by'] = auth()->user()->id;
            $album['photos'][$photoIndex]['approved_at'] = now()->toDateTimeString();

            $photos[$albumIndex] = $album;
            
            // Update photos array
            $project->photos = $photos;
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo approved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving photo: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'album_index' => $albumIndex,
                'photo_index' => $photoIndex,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An error occurred while approving the photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function disapprovePhoto(Request $request, $projectId, int $albumIndex, int $photoIndex)
    {
        // Log immediately to see if method is called
        Log::info('=== disapprovePhoto METHOD CALLED ===', [
            'project_id_param' => $projectId,
            'album_index' => $albumIndex,
            'photo_index' => $photoIndex,
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'all_params' => $request->all(),
        ]);
        
        // Get project manually to avoid route model binding issues
        $project = Project::find($projectId);
        if (!$project) {
            Log::error('Project not found', ['project_id' => $projectId]);
            return response()->json(['error' => 'Project not found.'], 404);
        }
        
        // Log the request for debugging
        Log::info('disapprovePhoto processing', [
            'project_id' => $project->id,
            'album_index' => $albumIndex,
            'photo_index' => $photoIndex,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? null,
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'accept_header' => $request->header('Accept'),
        ]);
        
        // Force JSON response for AJAX requests
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        
        try {
            // Check authorization - if it returns a response, return it
            $authResponse = $this->authorizeCompanyAccess($project);
            if ($authResponse) {
                return $authResponse;
            }
            
            // Only site engineers can disapprove photos
            if (auth()->user()->role !== 'site_engineer') {
                return response()->json(['error' => 'Only site engineers can disapprove photos.'], 403);
            }

            $photos = $project->photos ?? [];
            if (!is_array($photos) || !isset($photos[$albumIndex])) {
                return response()->json(['error' => 'Album not found.'], 404);
            }

            $album = $photos[$albumIndex];
            if (!isset($album['photos']) || !is_array($album['photos']) || !isset($album['photos'][$photoIndex])) {
                return response()->json(['error' => 'Photo not found.'], 404);
            }

            // Update approval status
            $album['photos'][$photoIndex]['approval_status'] = 'disapproved';
            $album['photos'][$photoIndex]['approved_by'] = auth()->user()->id;
            $album['photos'][$photoIndex]['approved_at'] = now()->toDateTimeString();

            $photos[$albumIndex] = $album;
            
            // Update photos array
            $project->photos = $photos;
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo disapproved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error disapproving photo: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'album_index' => $albumIndex,
                'photo_index' => $photoIndex,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An error occurred while disapproving the photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkApprovePhotos(Request $request, Project $project, int $albumIndex)
    {
        // Force JSON response for AJAX requests - set multiple ways to ensure detection
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        
        try {
            // Check authorization - if it returns a response, return it
            $authResponse = $this->authorizeCompanyAccess($project);
            if ($authResponse) {
                return $authResponse;
            }
            
            // Only site engineers can approve photos
            if (auth()->user()->role !== 'site_engineer') {
                return response()->json(['error' => 'Only site engineers can approve photos.'], 403);
            }

            // Validate request - catch validation exceptions and return JSON
            try {
                $validated = $request->validate([
                    'photo_indices' => 'required|array|min:1',
                    'photo_indices.*' => 'integer',
                    'action' => 'required|in:approve,disapprove',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            $photos = $project->photos ?? [];
            if (!is_array($photos) || !isset($photos[$albumIndex])) {
                return response()->json(['error' => 'Album not found.'], 404);
            }

            $album = $photos[$albumIndex];
            if (!isset($album['photos']) || !is_array($album['photos'])) {
                return response()->json(['error' => 'Album has no photos.'], 404);
            }

            $action = $request->input('action');
            $status = $action === 'approve' ? 'approved' : 'disapproved';
            $updatedCount = 0;

            foreach ($request->input('photo_indices') as $photoIndex) {
                $photoIndex = (int) $photoIndex;
                if (isset($album['photos'][$photoIndex])) {
                    $album['photos'][$photoIndex]['approval_status'] = $status;
                    $album['photos'][$photoIndex]['approved_by'] = auth()->user()->id;
                    $album['photos'][$photoIndex]['approved_at'] = now()->toDateTimeString();
                    $updatedCount++;
                }
            }

            if ($updatedCount === 0) {
                return response()->json(['error' => 'No photos were updated.'], 400);
            }

            $photos[$albumIndex] = $album;
            $project->photos = $photos;
            $project->save();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} photo(s) {$action}d successfully.",
                'updated_count' => $updatedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk approving photos: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'album_index' => $albumIndex,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An error occurred while updating photos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Project $project)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit projects.');
        }

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'budget' => $project->budget,
                    'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : null,
                    'company_id' => $project->company_id,
                    'files' => $project->files ?? [],
                ],
                'statuses' => Project::statusOptions(),
            ]);
        }

        // Redirect to index since we're using modals now
        return redirect()->route('admin.projects.index');
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeCompanyAccess($project);
        
        // Ensure only admin or super_admin can update projects
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can update projects.');
        }
        
        $validated = $this->validateProject($request, $project);
        $validated['updated_by'] = auth()->id();

        // Handle file uploads
        $files = $this->handleFileUploads($request, $project);
        if ($files !== null) {
            $validated['files'] = $files;
        }

        // Photo gallery is now managed separately via gallery page

        try {
            $project->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                $errorMessage = 'Cannot update project: Invalid company or related data. Please check your selections.';
                
                // Return JSON response for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['company_id' => [$errorMessage]]
                    ], 422);
                }
                
                return back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
            throw $e;
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $project->refresh();
            $project->load('company');
            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client_name' => $project->client_name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'budget' => $project->budget,
                    'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : null,
                    'company_id' => $project->company_id,
                    'company' => $project->company ? [
                        'id' => $project->company->id,
                        'name' => $project->company->name,
                    ] : null,
                    'files' => $project->files ?? [],
                ]
            ]);
        }

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeCompanyAccess($project);
        
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete projects.');
        }
        
        // Delete all photos from storage before deleting the project
        if ($project->photos && is_array($project->photos)) {
            foreach ($project->photos as $album) {
                if (isset($album['photos']) && is_array($album['photos'])) {
                    foreach ($album['photos'] as $photo) {
                        $photoPath = $photo['path'] ?? null;
                        if ($photoPath) {
                            // Delete from storage/app/public (root storage directory)
                            $this->deletePhotoFile($photoPath);
                        }
                    }
                }
            }
        }
        
        // Delete all files from storage before deleting the project
        if ($project->files && is_array($project->files)) {
            foreach ($project->files as $file) {
                $filePath = $file['path'] ?? null;
                if ($filePath) {
                    try {
                        $storage = \Storage::disk('public');
                        if ($storage->exists($filePath)) {
                            $storage->delete($filePath);
                        }
                        // Fallback: Delete directly from file system
                        $fullPath = storage_path('app/public/' . $filePath);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error deleting project file: {$filePath}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    protected function validateProject(Request $request, ?Project $project = null): array
    {
        $statusRule = implode(',', Project::statusOptions());

        return $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . $statusRule,
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
    }

    public function switch(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $projectId = $request->input('project_id');
        
        // Verify project belongs to active company and user has access
        if ($projectId) {
            $activeCompanyId = CompanyContext::getActiveCompanyId();
            $project = Project::find($projectId);
            
            if (!$project) {
                return back()->with('error', 'Project not found.');
            }
            
            // Check company access
            if ($activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
                return back()->with('error', 'Project does not belong to active company.');
            }
            
            // Check project access for non-super admins
            $user = auth()->user();
            if (!$user->isSuperAdmin() && !$user->hasProjectAccess($project->id)) {
                return back()->with('error', 'You do not have access to this project.');
            }
        }

        ProjectContext::setActiveProjectId($projectId ? (int) $projectId : null);
        
        return back()->with('success', $projectId ? 'Active project switched.' : 'Project filter cleared.');
    }

    /**
     * Authorize company access for a project
     * Returns a JSON response for AJAX requests if access is denied, otherwise aborts
     * 
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse|null Returns JSON response for AJAX if denied, null if allowed
     */
    protected function authorizeCompanyAccess(Project $project)
    {
        $user = auth()->user();
        $request = request();
        
        // Super admins have access to all projects
        if ($user->isSuperAdmin()) {
            return null;
        }

        // Check company access
        $activeCompanyId = CompanyContext::getActiveCompanyId();
        if ($activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
            // For AJAX/JSON requests, return JSON response directly
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'You do not have access to this project.'], 403);
            }
            abort(403, 'You do not have access to this project.');
        }

        // Check project-specific access using the helper method
        if (!$user->hasProjectAccess($project->id)) {
            // For AJAX/JSON requests, return JSON response directly
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'You do not have access to this project.'], 403);
            }
            abort(403, 'You do not have access to this project.');
        }
        
        return null; // Access granted
    }

    /**
     * Handle file uploads for projects
     */
    protected function handleFileUploads(Request $request, ?Project $project = null): ?array
    {
        $existingFiles = $project ? ($project->files ?? []) : [];
        $fileNames = $request->input('file_names', []);
        $fileInputs = $request->file('files', []);
        $deleteFiles = $request->input('delete_files', []);
        $existingFileIndices = $request->input('existing_file_indices', []);

        // Remove deleted files
        if (!empty($deleteFiles)) {
            foreach ($deleteFiles as $index) {
                $index = (int) $index;
                if (isset($existingFiles[$index])) {
                    // Delete physical file
                    $filePath = $existingFiles[$index]['path'] ?? null;
                    if ($filePath && \Storage::disk('public')->exists($filePath)) {
                        \Storage::disk('public')->delete($filePath);
                    }
                    unset($existingFiles[$index]);
                }
            }
        }

        // Update existing files with new names and/or files
        $updatedFiles = [];
        $fileInputIndex = 0;
        
        foreach ($fileNames as $nameIndex => $fileName) {
            // Check if this is an existing file
            if (isset($existingFileIndices[$nameIndex])) {
                $existingIndex = (int) $existingFileIndices[$nameIndex];
                if (isset($existingFiles[$existingIndex])) {
                    $existingFile = $existingFiles[$existingIndex];
                    
                    // Check if a new file was uploaded for this existing file
                    $newFile = null;
                    if (isset($fileInputs[$fileInputIndex]) && $fileInputs[$fileInputIndex]->isValid()) {
                        $newFile = $fileInputs[$fileInputIndex];
                        $fileInputIndex++;
                    }
                    
                    if ($newFile) {
                        // Delete old file
                        $oldPath = $existingFile['path'] ?? null;
                        if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
                            \Storage::disk('public')->delete($oldPath);
                        }
                        
                        // Store new file
                        $path = $newFile->store('projects/files', 'public');
                        $updatedFiles[] = [
                            'name' => $fileName ?: ($existingFile['name'] ?? 'Document'),
                            'path' => $path,
                            'original_name' => $newFile->getClientOriginalName(),
                            'size' => $newFile->getSize(),
                            'mime_type' => $newFile->getMimeType(),
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                    } else {
                        // Keep existing file, just update name
                        $updatedFiles[] = array_merge($existingFile, [
                            'name' => $fileName ?: ($existingFile['name'] ?? 'Document'),
                        ]);
                    }
                }
            } else {
                // New file upload
                if (isset($fileInputs[$fileInputIndex]) && $fileInputs[$fileInputIndex]->isValid()) {
                    $file = $fileInputs[$fileInputIndex];
                    $fileInputIndex++;
                    
                    $path = $file->store('projects/files', 'public');
                    $updatedFiles[] = [
                        'name' => $fileName ?: 'Document ' . (count($updatedFiles) + 1),
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        return !empty($updatedFiles) ? $updatedFiles : null;
    }

    /**
     * Handle photo gallery uploads for projects
     */
    protected function handlePhotoUploads(Request $request, ?Project $project = null): ?array
    {
        $existingAlbums = $project ? ($project->photos ?? []) : [];
        $albumNames = $request->input('album_names', []);
        $albumPhotos = $request->file('album_photos', []);
        $deleteAlbums = $request->input('delete_albums', []);
        $deletePhotos = $request->input('delete_photos', []);
        $existingAlbumIndices = $request->input('existing_album_indices', []);
        $existingPhotos = $request->input('existing_photos', []);

        // Remove deleted albums
        if (!empty($deleteAlbums)) {
            foreach ($deleteAlbums as $albumIndex) {
                $albumIndex = (int) $albumIndex;
                if (isset($existingAlbums[$albumIndex])) {
                    // Delete all photos in this album
                    $album = $existingAlbums[$albumIndex];
                    if (isset($album['photos']) && is_array($album['photos'])) {
                        foreach ($album['photos'] as $photo) {
                            $photoPath = $photo['path'] ?? null;
                            if ($photoPath) {
                                // Delete from storage/app/public (root storage directory)
                                $this->deletePhotoFile($photoPath);
                            }
                        }
                    }
                    unset($existingAlbums[$albumIndex]);
                }
            }
        }

        // Remove deleted photos from albums (only admins can delete photos)
        if (!empty($deletePhotos)) {
            $user = auth()->user();
            // Only process deletions if user is admin or super_admin
            if ($user && $user->isAdmin()) {
            foreach ($deletePhotos as $photoKey) {
                // Format: album_index-photo_index
                $parts = explode('-', $photoKey);
                if (count($parts) === 2) {
                    $formAlbumIndex = (int) $parts[0];
                    $photoIndex = (int) $parts[1];
                    
                    // Find the existing album index for this form album
                    $existingAlbumIndex = $existingAlbumIndices[$formAlbumIndex] ?? null;
                    if ($existingAlbumIndex !== null && isset($existingAlbums[$existingAlbumIndex]['photos'][$photoIndex])) {
                        $photoPath = $existingAlbums[$existingAlbumIndex]['photos'][$photoIndex]['path'] ?? null;
                            if ($photoPath) {
                                // Delete from storage/app/public (root storage directory)
                                $this->deletePhotoFile($photoPath);
                        }
                        unset($existingAlbums[$existingAlbumIndex]['photos'][$photoIndex]);
                        $existingAlbums[$existingAlbumIndex]['photos'] = array_values($existingAlbums[$existingAlbumIndex]['photos']);
                        }
                    }
                }
            }
        }

        // Process album updates and new albums
        $updatedAlbums = [];

        foreach ($albumNames as $formAlbumIndex => $albumName) {
            if (empty($albumName)) {
                continue;
            }

            $album = [
                'name' => $albumName,
                'photos' => [],
            ];

            // Check if this is an existing album
            $existingAlbumIndex = $existingAlbumIndices[$formAlbumIndex] ?? null;
            if ($existingAlbumIndex !== null && isset($existingAlbums[$existingAlbumIndex])) {
                // Keep existing photos that weren't deleted
                $album['photos'] = $existingAlbums[$existingAlbumIndex]['photos'] ?? [];
            }

            // Add new photos for this album
            if (isset($albumPhotos[$formAlbumIndex]) && is_array($albumPhotos[$formAlbumIndex])) {
                foreach ($albumPhotos[$formAlbumIndex] as $photo) {
                    if ($photo && $photo->isValid()) {
                        // Compress and resize image before storing
                        $compressedPath = $this->compressAndStoreImage($photo, 'projects/photos');
                        if ($compressedPath) {
                        $album['photos'][] = [
                                'path' => $compressedPath,
                            'original_name' => $photo->getClientOriginalName(),
                                'size' => \Storage::disk('public')->size($compressedPath),
                                'mime_type' => 'image/jpeg', // Compressed images are saved as JPEG
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                        }
                    }
                }
            }

            if (!empty($album['photos']) || !empty($album['name'])) {
                $updatedAlbums[] = $album;
            }
        }

        return !empty($updatedAlbums) ? $updatedAlbums : null;
    }

    /**
     * Compress and resize image before storing
     * Aggressively reduces file size to under 10 KB while maintaining acceptable quality
     * 
     * @param \Illuminate\Http\UploadedFile $file The uploaded image file
     * @param string $directory Storage directory (e.g., 'projects/photos')
     * @return string|null The storage path or null on failure
     */
    protected function compressAndStoreImage(\Illuminate\Http\UploadedFile $file, string $directory): ?string
    {
        try {
            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                \Log::warning('GD extension not available, storing image without compression');
                return $file->store($directory, 'public');
            }

            // Get image info
            $imageInfo = getimagesize($file->getRealPath());
            if (!$imageInfo) {
                \Log::error('Unable to get image info for: ' . $file->getClientOriginalName());
                return $file->store($directory, 'public');
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // Aggressive maximum dimensions for under 10KB target
            // Start with smaller dimensions and adjust if needed
            $maxWidth = 800;
            $maxHeight = 800;
            $targetSizeKB = 8; // Target 8 KB (under 10KB)

            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            // Ensure minimum dimensions for very small images
            if ($newWidth < 100) $newWidth = 100;
            if ($newHeight < 100) $newHeight = 100;

            // Create image resource based on MIME type
            $sourceImage = null;
            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($file->getRealPath());
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($file->getRealPath());
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = imagecreatefromwebp($file->getRealPath());
                    }
                    break;
                default:
                    \Log::warning('Unsupported image type: ' . $mimeType);
                    return $file->store($directory, 'public');
            }

            if (!$sourceImage) {
                \Log::error('Failed to create image resource');
                return $file->store($directory, 'public');
            }

            // Create new image with calculated dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // For aggressive compression, convert to RGB (no transparency)
            // This reduces file size significantly
            $white = imagecolorallocate($newImage, 255, 255, 255);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);

            // Resize image with high-quality resampling
            imagecopyresampled(
                $newImage,
                $sourceImage,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            // Generate filename
            $extension = 'jpg';
            $filename = \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $filename = $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
            $storagePath = $directory . '/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);

            // Ensure directory exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Aggressive quality adjustment to achieve under 10KB
            $quality = 50; // Start with lower quality (50%)
            $minQuality = 30; // Minimum quality threshold
            $maxQuality = 70; // Maximum quality
            $attempts = 0;
            $maxAttempts = 15;
            $bestQuality = $quality;
            $bestSize = PHP_INT_MAX;

            // First pass: Find approximate quality
            do {
                // Save as JPEG
                imagejpeg($newImage, $fullPath, $quality);

                // Check file size
                $fileSizeKB = filesize($fullPath) / 1024;
                $attempts++;

                if ($fileSizeKB < $bestSize && $fileSizeKB <= $targetSizeKB * 1.2) {
                    $bestSize = $fileSizeKB;
                    $bestQuality = $quality;
                }

                // Adjust quality based on file size
                if ($fileSizeKB > $targetSizeKB * 1.2) {
                    // File too large, reduce quality more aggressively
                    $quality -= 5;
                    if ($quality < $minQuality) {
                        // If still too large at minimum quality, reduce dimensions
                        $newWidth = (int)($newWidth * 0.9);
                        $newHeight = (int)($newHeight * 0.9);
                        if ($newWidth < 50 || $newHeight < 50) {
                            break; // Too small, use current result
                        }
                        // Recreate image with smaller dimensions
                        imagedestroy($newImage);
                        $newImage = imagecreatetruecolor($newWidth, $newHeight);
                        $white = imagecolorallocate($newImage, 255, 255, 255);
                        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);
                        imagecopyresampled(
                            $newImage,
                            $sourceImage,
                            0, 0, 0, 0,
                            $newWidth,
                            $newHeight,
                            $originalWidth,
                            $originalHeight
                        );
                        $quality = 50; // Reset quality after resize
                    }
                } elseif ($fileSizeKB < $targetSizeKB * 0.5 && $quality < $maxQuality) {
                    // File too small, can increase quality slightly
                    $quality += 3;
                } else {
                    // Within acceptable range
                    break;
                }
            } while ($attempts < $maxAttempts);

            // Use best quality found
            if ($bestSize < PHP_INT_MAX) {
                imagejpeg($newImage, $fullPath, $bestQuality);
                $fileSizeKB = filesize($fullPath) / 1024;
            }

            // Clean up memory
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            \Log::info("Image compressed: {$file->getClientOriginalName()} -> {$fileSizeKB}KB (quality: {$bestQuality}, dimensions: {$newWidth}x{$newHeight})");

            return $storagePath;

        } catch (\Exception $e) {
            \Log::error('Error compressing image: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            // Fallback to original storage method
            return $file->store($directory, 'public');
        }
    }

    /**
     * Delete photo file from storage root directory
     * Ensures file is deleted from storage/app/public (root storage)
     * 
     * @param string $photoPath The relative path from storage/app/public (e.g., 'projects/photos/image.jpg')
     * @return bool True if file was deleted or didn't exist, false on error
     */
    protected function deletePhotoFile(string $photoPath): bool
    {
        if (empty($photoPath)) {
            return false;
        }

        try {
            // Clean the path (remove leading/trailing slashes)
            $photoPath = trim($photoPath, '/');
            
            // Method 1: Use Laravel Storage facade (primary method)
            $storage = \Storage::disk('public');
            if ($storage->exists($photoPath)) {
                $deleted = $storage->delete($photoPath);
                if ($deleted) {
                    \Log::info("Photo deleted via Storage facade: {$photoPath}");
                    return true;
                }
            }
            
            // Method 2: Fallback - Delete directly from file system (root storage)
            $fullPath = storage_path('app/public/' . $photoPath);
            if (file_exists($fullPath)) {
                $deleted = @unlink($fullPath);
                if ($deleted) {
                    \Log::info("Photo deleted via file system: {$fullPath}");
                    return true;
                } else {
                    \Log::warning("Failed to delete photo file: {$fullPath}");
                    return false;
                }
            }
            
            // File doesn't exist (already deleted or never existed)
            \Log::info("Photo file not found (may already be deleted): {$photoPath}");
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Error deleting photo file: {$photoPath}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}

