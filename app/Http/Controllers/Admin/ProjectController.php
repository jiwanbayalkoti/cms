<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Company;
use App\Models\Project;
use App\Support\CompanyContext;
use App\Support\ProjectContext;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ValidatesForms;
    
    public function __construct()
    {
        $this->middleware('admin');
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
                $accessibleProjectIds = $user->getAccessibleProjectIds();
                if ($accessibleProjectIds !== null) {
                    $query->whereIn('id', $accessibleProjectIds);
                }
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
        
        // Get companies with their projects
        $companiesWithProjects = collect();
        foreach ($projectsByCompany as $compId => $projects) {
            $company = $compId ? Company::find($compId) : null;
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
        return view('admin.projects.create', [
            'statuses' => Project::statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
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
            Project::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                return back()
                    ->withInput()
                    ->with('error', 'Cannot create project: Invalid company selected. Please ensure you have selected a valid company.');
            }
            throw $e;
        }

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        return view('admin.projects.show', [
            'project' => $project->load(['company', 'creator', 'updater']),
        ]);
    }

    public function gallery(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        return view('admin.projects.gallery', [
            'project' => $project->load(['company']),
        ]);
    }

    public function edit(Project $project)
    {
        $this->authorizeCompanyAccess($project);

        return view('admin.projects.edit', [
            'project' => $project->load('company'),
            'statuses' => Project::statusOptions(),
        ]);
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

        // Handle photo gallery uploads (only admins can delete photos)
        $photos = $this->handlePhotoUploads($request, $project);
        if ($photos !== null) {
            $validated['photos'] = $photos;
        }

        try {
            $project->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Foreign key constraint violation
                return back()
                    ->withInput()
                    ->with('error', 'Cannot update project: Invalid company or related data. Please check your selections.');
            }
            throw $e;
        }

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorizeCompanyAccess($project);
        
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

    protected function authorizeCompanyAccess(Project $project): void
    {
        $user = auth()->user();
        
        // Super admins have access to all projects
        if ($user->isSuperAdmin()) {
            return;
        }

        // Check company access
        $activeCompanyId = CompanyContext::getActiveCompanyId();
        if ($activeCompanyId && (int) $activeCompanyId !== 1 && $project->company_id !== $activeCompanyId) {
            abort(403, 'You do not have access to this project.');
        }

        // Check project-specific access using the helper method
        if (!$user->hasProjectAccess($project->id)) {
            abort(403, 'You do not have access to this project.');
        }
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
     * Reduces file size to approximately 200-300 KB while maintaining quality
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

            // Maximum dimensions (maintains aspect ratio)
            $maxWidth = 1920;
            $maxHeight = 1920;
            $targetSizeKB = 250; // Target 250 KB (middle of 200-300 KB range)

            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

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

            // Preserve transparency for PNG and GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resize image
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

            // Save with quality adjustment to achieve target size
            $quality = 85; // Start with 85% quality
            $minQuality = 60;
            $maxQuality = 95;
            $attempts = 0;
            $maxAttempts = 10;

            do {
                // Save as JPEG
                imagejpeg($newImage, $fullPath, $quality);

                // Check file size
                $fileSizeKB = filesize($fullPath) / 1024;
                $attempts++;

                // Adjust quality based on file size
                if ($fileSizeKB > $targetSizeKB * 1.2 && $quality > $minQuality) {
                    // File too large, reduce quality
                    $quality -= 5;
                } elseif ($fileSizeKB < $targetSizeKB * 0.7 && $quality < $maxQuality) {
                    // File too small, increase quality
                    $quality += 5;
                } else {
                    // Within acceptable range (70% to 120% of target)
                    break;
                }
            } while ($attempts < $maxAttempts && ($fileSizeKB > $targetSizeKB * 1.2 || $fileSizeKB < $targetSizeKB * 0.7));

            // Clean up memory
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            \Log::info("Image compressed: {$file->getClientOriginalName()} -> {$fileSizeKB}KB (quality: {$quality})");

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

