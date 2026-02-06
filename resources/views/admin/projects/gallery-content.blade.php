@php
    use App\Support\CompanyContext;
    use Illuminate\Support\Facades\Storage;
    use App\Helpers\StorageHelper;

    $activeCompanyId = CompanyContext::getActiveCompanyId();
    $isAdmin = auth()->user()->isAdmin();
    $canAddAlbum = true;
@endphp

<style>
    /* Hide text on mobile for gallery action buttons */
    @media (max-width: 768px) {
        .gallery-btn-mobile .btn-text {
            display: none !important;
        }
        .gallery-btn-mobile svg {
            margin-right: 0 !important;
        }
        .gallery-btn-mobile {
            padding: 0.5rem !important;
            min-width: 40px;
            justify-content: center;
        }
        
        /* Fix status badge cropping on mobile */
        .album-item .bg-gray-50 {
            overflow: visible !important;
        }
        
        .album-item .flex.items-center.justify-between {
            min-width: 0;
            overflow: visible;
        }
        
        .album-item .bg-green-600,
        .album-item .bg-red-600,
        .album-item .bg-yellow-500 {
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: nowrap !important;
        }
        
        /* Hide text labels, show only icons/badges on mobile */
        .status-label {
            display: none !important;
        }
        
        .status-text {
            display: none !important;
        }
        
        .status-icon {
            display: inline !important;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
    }
</style>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-sm md:text-base lg:text-lg xl:text-xl font-bold text-gray-900">{{ $project->name }} - Photo Gallery</h1>
        <p class="text-sm md:text-base text-gray-600 mt-2">{{ $project->client_name ?? 'Project Gallery' }}</p>
    </div>
    <div class="flex items-center gap-3">
        @if(Auth::user()->role !== 'site_engineer')
        <button onclick="closeGalleryModal(); openViewProjectModal({{ $project->id }});" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200 gallery-btn-mobile inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <span class="btn-text">View Project Details</span>
        </button>
        @endif
        @if($isAdmin)
        <button onclick="closeGalleryModal(); openEditProjectModal({{ $project->id }});" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 gallery-btn-mobile inline-flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="btn-text">Edit Project</span>
        </button>
        @endif
        <button type="button" onclick="showAddAlbumModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 gallery-btn-mobile inline-flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="btn-text">Add Album</span>
        </button>
    </div>
</div>

<div id="albums-container" class="space-y-4">
    @if($project->photos && is_array($project->photos) && count($project->photos) > 0)
        @foreach(array_reverse($project->photos, true) as $albumIndex => $album)
            @include('admin.projects.partials.album-item', [
                'album' => $album,
                'albumIndex' => $albumIndex,
                'isFirst' => $loop->first,
                'project' => $project,
                'isAdmin' => $isAdmin,
                'isSiteEngineer' => Auth::user()->role === 'site_engineer'
            ])
        @endforeach
    @else
        <div class="bg-white shadow-lg rounded-lg p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-4 text-base md:text-lg font-medium text-gray-900">No photos found</h3>
            <p class="mt-2 text-sm text-gray-500">This project doesn't have any photo albums yet.</p>
            <div class="mt-6">
                <button onclick="showAddAlbumModal()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Photo Album
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Add Album Modal -->
<div id="addAlbumModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg md:text-xl font-semibold text-gray-900">Add New Album</h3>
            <button onclick="closeAddAlbumModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="addAlbumForm" onsubmit="addAlbum(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                <input type="text" name="name" id="albumName" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="e.g., Foundation, Beam, DBC">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Photos (Optional - can add later)</label>
                <input type="file" name="photos[]" id="albumPhotos" multiple accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="closeAddAlbumModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add Album
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Album Confirmation Modal -->
<div id="deleteAlbumConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeDeleteAlbumConfirmation()">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-base md:text-lg font-semibold text-center mb-2">Delete Album</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete the album <strong id="deleteAlbumName"></strong>? All photos in this album will be permanently deleted. This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteAlbumConfirmation()" class="btn btn-secondary">Cancel</button>
                <button onclick="confirmDeleteAlbum()" class="btn btn-danger">Delete Album</button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex flex-col items-center justify-center p-4" onclick="closeLightbox()">
    <div class="w-full max-w-6xl flex-1 flex flex-col min-h-0 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-end gap-2 absolute top-2 right-2 z-20">
            <button type="button" onclick="event.stopPropagation(); lightboxZoom(0.25)" class="text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70" title="Zoom In">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path></svg>
            </button>
            <button type="button" onclick="event.stopPropagation(); lightboxZoom(-0.25)" class="text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70" title="Zoom Out">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
            </button>
            <button type="button" onclick="event.stopPropagation(); lightboxRotate(-90)" class="text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70" title="Rotate Left">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            </button>
            <button type="button" onclick="event.stopPropagation(); lightboxRotate(90)" class="text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70" title="Rotate Right">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
            </button>
            <button onclick="closeLightbox()" class="text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-70">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <button id="lightbox-prev" onclick="event.stopPropagation(); navigateLightbox(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button id="lightbox-next" onclick="event.stopPropagation(); navigateLightbox(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
        <div id="lightbox-image-wrap" class="flex-1 overflow-auto flex items-center justify-center min-h-0 my-2">
            <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full rounded-lg transition-transform duration-200 origin-center">
        </div>
        <div id="lightbox-thumbnails" class="flex gap-2 justify-center overflow-x-auto py-2 px-2 shrink-0" style="max-height: 70px; min-height: 56px;"></div>
        <p id="lightbox-caption" class="text-white text-center mt-2 text-sm md:text-base lg:text-lg"></p>
        <p id="lightbox-counter" class="text-white text-center mt-1 text-sm opacity-75"></p>
    </div>
</div>

<script>
(function() {
    const projectId = {{ $project->id }};
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Set up CSRF token for all fetch requests
    const defaultHeaders = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    };

    // Define toggleAlbum and make it available globally immediately
    window.toggleAlbum = function(albumIndex) {
        const content = document.getElementById('album-content-' + albumIndex);
        const icon = document.getElementById('toggle-icon-' + albumIndex);
        
        if (!content || !icon) {
            console.error('Album content or icon not found for index:', albumIndex);
            return;
        }
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    };

    window.showAddAlbumModal = function() {
        const modal = document.getElementById('addAlbumModal');
        if (modal) {
            modal.classList.remove('hidden');
            const albumName = document.getElementById('albumName');
            if (albumName) albumName.focus();
        }
    };

    window.closeAddAlbumModal = function() {
        const modal = document.getElementById('addAlbumModal');
        if (modal) {
            modal.classList.add('hidden');
            const form = document.getElementById('addAlbumForm');
            if (form) form.reset();
        }
    };

    window.addAlbum = function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('name', document.getElementById('albumName').value);
        formData.append('_token', csrfToken);
        
        const photos = document.getElementById('albumPhotos').files;
        for (let i = 0; i < photos.length; i++) {
            formData.append('photos[]', photos[i]);
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';

        fetch(`/admin/projects/${projectId}/gallery/album`, {
            method: 'POST',
            body: formData,
            headers: defaultHeaders
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload gallery content
                if (typeof openGalleryModal === 'function') {
                    openGalleryModal(projectId);
                } else {
                    location.reload();
                }
            } else {
                alert(data.error || 'Failed to add album');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Album';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the album');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Add Album';
        });
    };

    window.editAlbumName = function(albumIndex) {
        const albumHeader = document.querySelector(`[data-album-index="${albumIndex}"] .album-name`);
        const currentName = albumHeader.textContent.trim();
        const newName = prompt('Enter new album name:', currentName);
        
        if (newName && newName !== currentName) {
            const formData = new FormData();
            formData.append('name', newName);
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');

            fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(html => {
                        throw new Error('Server returned HTML instead of JSON');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    albumHeader.textContent = newName;
                    alert('Album name updated successfully');
                } else {
                    alert(data.error || 'Failed to update album name');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the album: ' + (error.message || error));
            });
        }
    };

    let deleteAlbumIndex = null;

    window.showDeleteAlbumConfirmation = function(albumIndex, albumName) {
        deleteAlbumIndex = albumIndex;
        document.getElementById('deleteAlbumName').textContent = albumName || 'this album';
        document.getElementById('deleteAlbumConfirmationModal').classList.remove('hidden');
    };

    window.closeDeleteAlbumConfirmation = function() {
        deleteAlbumIndex = null;
        document.getElementById('deleteAlbumConfirmationModal').classList.add('hidden');
    };

    window.confirmDeleteAlbum = function() {
        if (deleteAlbumIndex === null) return;

        const albumIndex = deleteAlbumIndex;
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'DELETE');

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeDeleteAlbumConfirmation();
                if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Album deleted successfully', 'success');
                } else {
                    alert(data.message || 'Album deleted successfully');
                }
                if (typeof openGalleryModal === 'function') {
                    openGalleryModal(projectId);
                } else {
                    location.reload();
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(data.error || 'Failed to delete album', 'error');
                } else {
                    alert(data.error || 'Failed to delete album');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while deleting the album', 'error');
            } else {
                alert('An error occurred while deleting the album');
            }
        });
    };

    window.addPhotosToAlbum = function(albumIndex) {
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = 'image/*';
        input.onchange = function(e) {
            const files = e.target.files;
            if (files.length === 0) return;

            const formData = new FormData();
            formData.append('_token', csrfToken);
            for (let i = 0; i < files.length; i++) {
                formData.append('photos[]', files[i]);
            }

            const loader = document.createElement('div');
            loader.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            loader.innerHTML = '<div class="bg-white rounded-lg p-6"><p class="text-gray-700">Uploading photos...</p></div>';
            document.body.appendChild(loader);

            fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}/photos`, {
                method: 'POST',
                body: formData,
                headers: defaultHeaders
            })
            .then(response => response.json())
            .then(data => {
                document.body.removeChild(loader);
                if (data.success) {
                    if (typeof openGalleryModal === 'function') {
                        openGalleryModal(projectId);
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.error || 'Failed to add photos');
                }
            })
            .catch(error => {
                document.body.removeChild(loader);
                console.error('Error:', error);
                alert('An error occurred while adding photos');
            });
        };
        input.click();
    };

    window.deletePhoto = function(albumIndex, photoIndex) {
        if (!confirm('Are you sure you want to delete this photo?')) {
            return;
        }

        const photoElement = document.querySelector(`[data-album-index="${albumIndex}"] [data-photo-index="${photoIndex}"]`);
        if (photoElement) {
            photoElement.style.opacity = '0.5';
            photoElement.style.pointerEvents = 'none';
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'DELETE');

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}/photo/${photoIndex}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => Promise.reject(data));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (photoElement) {
                    photoElement.remove();
                    const albumHeader = document.querySelector(`[data-album-index="${albumIndex}"]`);
                    const photoCount = albumHeader.querySelectorAll('[data-photo-index]').length;
                    const countSpan = albumHeader.querySelector('.text-sm.text-gray-500');
                    if (countSpan) {
                        countSpan.textContent = photoCount + ' ' + (photoCount === 1 ? 'photo' : 'photos');
                    }
                } else {
                    if (typeof openGalleryModal === 'function') {
                        openGalleryModal(projectId);
                    } else {
                        location.reload();
                    }
                }
            } else {
                if (photoElement) {
                    photoElement.style.opacity = '1';
                    photoElement.style.pointerEvents = 'auto';
                }
                alert(data.error || 'Failed to delete photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (photoElement) {
                photoElement.style.opacity = '1';
                photoElement.style.pointerEvents = 'auto';
            }
            const errorMsg = error.error || (typeof error === 'string' ? error : 'An error occurred while deleting the photo');
            alert(errorMsg);
        });
    };

    // Lightbox functions
    let allPhotos = [];
    let currentAlbumPhotos = [];
    let currentPhotoIndex = -1;
    let lightboxScale = 1;
    let lightboxRotateDeg = 0;

    function applyLightboxTransform() {
        const img = document.getElementById('lightbox-image');
        if (img) {
            img.style.transform = 'scale(' + lightboxScale + ') rotate(' + lightboxRotateDeg + 'deg)';
        }
    }

    window.lightboxZoom = function(delta) {
        lightboxScale = Math.min(4, Math.max(0.5, lightboxScale + delta));
        applyLightboxTransform();
    };

    window.lightboxRotate = function(deg) {
        lightboxRotateDeg = (lightboxRotateDeg + deg) % 360;
        applyLightboxTransform();
    };

    window.initializePhotoCollection = function() {
        allPhotos = [];
        const photos = document.querySelectorAll('img[data-photo-url]');
        photos.forEach(photo => {
            const src = photo.getAttribute('data-photo-url');
            const caption = photo.getAttribute('data-photo-caption') || 'Photo';
            const albumIndex = parseInt(photo.closest('[data-album-index]')?.getAttribute('data-album-index') || 0, 10);
            if (src) {
                allPhotos.push({ src: src, caption: caption, albumIndex: albumIndex });
            }
        });
    };

    window.openLightbox = function(imageSrc, caption, albumIndex, photoIndex) {
        if (allPhotos.length === 0) {
            initializePhotoCollection();
        }
        
        currentAlbumPhotos = allPhotos.filter(function(p) { return p.albumIndex === albumIndex; });
        currentPhotoIndex = currentAlbumPhotos.findIndex(function(p) { return p.src === imageSrc; });
        if (currentPhotoIndex === -1) {
            currentPhotoIndex = 0;
        }
        
        lightboxScale = 1;
        lightboxRotateDeg = 0;
        updateLightboxDisplay();
        document.getElementById('lightbox').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.goToLightboxIndex = function(idx) {
        if (idx >= 0 && idx < currentAlbumPhotos.length) {
            currentPhotoIndex = idx;
            updateLightboxDisplay();
        }
    };

    function updateLightboxThumbnails() {
        var container = document.getElementById('lightbox-thumbnails');
        if (!container || !currentAlbumPhotos.length) return;
        container.innerHTML = '';
        currentAlbumPhotos.forEach(function(photo, idx) {
            var el = document.createElement('button');
            el.type = 'button';
            el.className = 'shrink-0 rounded overflow-hidden border-2 transition-all focus:outline-none focus:ring-2 focus:ring-white ' + (idx === currentPhotoIndex ? 'border-white opacity-100 ring-2 ring-white' : 'border-transparent opacity-60 hover:opacity-90');
            el.style.width = '56px';
            el.style.height = '56px';
            el.onclick = function(e) { e.stopPropagation(); goToLightboxIndex(idx); };
            var thumb = document.createElement('img');
            thumb.src = photo.src;
            thumb.alt = photo.caption || '';
            thumb.className = 'w-full h-full object-cover';
            el.appendChild(thumb);
            container.appendChild(el);
        });
    }

    window.updateLightboxDisplay = function() {
        if (currentAlbumPhotos.length && currentPhotoIndex >= 0 && currentPhotoIndex < currentAlbumPhotos.length) {
            const photo = currentAlbumPhotos[currentPhotoIndex];
            const img = document.getElementById('lightbox-image');
            img.src = photo.src;
            img.style.transform = '';
            lightboxScale = 1;
            lightboxRotateDeg = 0;
            applyLightboxTransform();
            document.getElementById('lightbox-caption').textContent = photo.caption;
            document.getElementById('lightbox-counter').textContent = (currentPhotoIndex + 1) + ' / ' + currentAlbumPhotos.length;
            
            document.getElementById('lightbox-prev').style.display = currentPhotoIndex > 0 ? 'block' : 'none';
            document.getElementById('lightbox-next').style.display = currentPhotoIndex < currentAlbumPhotos.length - 1 ? 'block' : 'none';

            updateLightboxThumbnails();
        }
    };

    window.navigateLightbox = function(direction) {
        const newIndex = currentPhotoIndex + direction;
        if (newIndex >= 0 && newIndex < currentAlbumPhotos.length) {
            currentPhotoIndex = newIndex;
            updateLightboxDisplay();
        }
    };

    window.closeLightbox = function() {
        document.getElementById('lightbox').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentPhotoIndex = -1;
        currentAlbumPhotos = [];
        lightboxScale = 1;
        lightboxRotateDeg = 0;
    };

    // Mouse wheel zoom on lightbox image
    var wrap = document.getElementById('lightbox-image-wrap');
    if (wrap) {
        wrap.addEventListener('wheel', function(e) {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            e.preventDefault();
            lightboxZoom(e.deltaY > 0 ? -0.15 : 0.15);
        }, { passive: false });
    }

    // Photo Approval Functions
    window.approvePhoto = function(albumIndex, photoIndex) {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'POST');

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}/photo/${photoIndex}/approve`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => Promise.reject(data));
                } else {
                    return response.text().then(html => {
                        throw new Error('Server returned HTML instead of JSON');
                    });
                }
            }
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
        })
        .then(data => {
            if (data.success) {
                if (typeof openGalleryModal === 'function') {
                    openGalleryModal(projectId);
                } else {
                    location.reload();
                }
            } else {
                alert(data.error || 'Failed to approve photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.error || error.message || 'An error occurred while approving the photo';
            alert(errorMsg);
        });
    };

    window.disapprovePhoto = function(albumIndex, photoIndex) {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('_method', 'POST');

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}/photo/${photoIndex}/disapprove`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => Promise.reject(data));
                } else {
                    return response.text().then(html => {
                        throw new Error('Server returned HTML instead of JSON');
                    });
                }
            }
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
        })
        .then(data => {
            if (data.success) {
                if (typeof openGalleryModal === 'function') {
                    openGalleryModal(projectId);
                } else {
                    location.reload();
                }
            } else {
                alert(data.error || 'Failed to disapprove photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.error || error.message || 'An error occurred while disapproving the photo';
            alert(errorMsg);
        });
    };

    window.toggleSelectAll = function(albumIndex) {
        const selectAll = document.getElementById('select-all-' + albumIndex);
        const checkboxes = document.querySelectorAll(`[data-album-index="${albumIndex}"].photo-checkbox`);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkButtons(albumIndex);
    };

    window.updateBulkButtons = function(albumIndex) {
        const checkboxes = document.querySelectorAll(`[data-album-index="${albumIndex}"].photo-checkbox:checked`);
        const bulkButtons = document.querySelectorAll(`[onclick*="bulkApprovePhotos(${albumIndex}"]`);
        
        bulkButtons.forEach(button => {
            button.disabled = checkboxes.length === 0;
            button.style.opacity = checkboxes.length === 0 ? '0.5' : '1';
            button.style.cursor = checkboxes.length === 0 ? 'not-allowed' : 'pointer';
        });
    };

    window.bulkApprovePhotos = function(albumIndex, action) {
        const checkboxes = document.querySelectorAll(`[data-album-index="${albumIndex}"].photo-checkbox:checked`);
        
        if (checkboxes.length === 0) {
            alert('Please select at least one photo');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${checkboxes.length} photo(s)?`)) {
            return;
        }
        
        const photoIndices = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-photo-index')));
        
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('action', action);
        photoIndices.forEach(index => {
            formData.append('photo_indices[]', index);
        });

        const submitBtn = event.target;
        submitBtn.disabled = true;
        submitBtn.textContent = action === 'approve' ? 'Approving...' : 'Disapproving...';

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}/photos/bulk-approve`, {
            method: 'POST',
            body: formData,
            headers: defaultHeaders
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
        })
        .then(data => {
            if (data.success) {
                if (typeof openGalleryModal === 'function') {
                    openGalleryModal(projectId);
                } else {
                    location.reload();
                }
            } else {
                alert(data.error || `Failed to ${action} photos`);
                submitBtn.disabled = false;
                submitBtn.textContent = action === 'approve' ? 'Approve Selected' : 'Disapprove Selected';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || `An error occurred while ${action}ing photos`;
            alert(errorMsg);
            submitBtn.disabled = false;
            submitBtn.textContent = action === 'approve' ? 'Approve Selected' : 'Disapprove Selected';
        });
    };

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        const firstIcon = document.getElementById('toggle-icon-0');
        if (firstIcon) {
            firstIcon.classList.add('rotate-180');
        }
        
        const albums = document.querySelectorAll('[data-album-index]');
        albums.forEach(album => {
            const albumIndex = album.getAttribute('data-album-index');
            updateBulkButtons(albumIndex);
        });
    });

    // Keyboard navigation
    // Close delete modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('deleteAlbumConfirmationModal').classList.contains('hidden')) {
                closeDeleteAlbumConfirmation();
                return;
            }
            closeLightbox();
            closeAddAlbumModal();
        } else if (e.key === 'ArrowLeft' && !document.getElementById('lightbox').classList.contains('hidden')) {
            navigateLightbox(-1);
        } else if (e.key === 'ArrowRight' && !document.getElementById('lightbox').classList.contains('hidden')) {
            navigateLightbox(1);
        }
    });
})();
</script>

