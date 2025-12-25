@extends('admin.layout')

@section('title', 'Project Gallery - ' . $project->name)

@section('content')
@php
    use App\Support\CompanyContext;
    use Illuminate\Support\Facades\Storage;
    use App\Helpers\StorageHelper;

    $activeCompanyId = CompanyContext::getActiveCompanyId();
    $isAdmin = auth()->user()->isAdmin();
    // Allow all users to add albums
    $canAddAlbum = true;
@endphp
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }} - Photo Gallery</h1>
        <p class="text-gray-600 mt-2">{{ $project->client_name ?? 'Project Gallery' }}</p>
    </div>
    <div class="flex items-center gap-3">
        @if(Auth::user()->role !== 'site_engineer')
        <a href="{{ route('admin.projects.show', $project) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
            View Project Details
        </a>
        @endif
        @if($isAdmin)
        <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
            Edit Project
        </a>
        @endif
        <button type="button" onclick="showAddAlbumModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
            <svg class="h-5 w-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Album
        </button>
    </div>
</div>

<div id="albums-container" class="space-y-4">
    @if($project->photos && is_array($project->photos) && count($project->photos) > 0)
        @foreach($project->photos as $albumIndex => $album)
            @include('admin.projects.partials.album-item', ['album' => $album, 'albumIndex' => $albumIndex, 'project' => $project, 'isAdmin' => $isAdmin])
        @endforeach
    @else
        <div class="bg-white shadow-lg rounded-lg p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No photos found</h3>
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
            <h3 class="text-xl font-semibold text-gray-900">Add New Album</h3>
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

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4" onclick="closeLightbox()">
    <div class="max-w-6xl w-full relative" onclick="event.stopPropagation()">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-screen mx-auto rounded-lg">
        <p id="lightbox-caption" class="text-white text-center mt-4 text-lg"></p>
    </div>
</div>

@push('scripts')
<script>
const projectId = {{ $project->id }};
const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Set up CSRF token for all fetch requests
const defaultHeaders = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json'
};

function toggleAlbum(albumIndex) {
    const content = document.getElementById('album-content-' + albumIndex);
    const icon = document.getElementById('toggle-icon-' + albumIndex);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

function showAddAlbumModal() {
    document.getElementById('addAlbumModal').classList.remove('hidden');
    document.getElementById('albumName').focus();
}

function closeAddAlbumModal() {
    document.getElementById('addAlbumModal').classList.add('hidden');
    document.getElementById('addAlbumForm').reset();
}

function addAlbum(e) {
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
            location.reload();
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
}

function editAlbumName(albumIndex) {
    const albumHeader = document.querySelector(`[data-album-index="${albumIndex}"] .album-name`);
    const currentName = albumHeader.textContent.trim();
    const newName = prompt('Enter new album name:', currentName);
    
    if (newName && newName !== currentName) {
        const formData = new FormData();
        formData.append('name', newName);
        formData.append('_token', csrfToken);

        fetch(`/admin/projects/${projectId}/gallery/album/${albumIndex}`, {
            method: 'PUT',
            body: formData,
            headers: defaultHeaders
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                albumHeader.textContent = newName;
            } else {
                alert(data.error || 'Failed to update album name');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the album');
        });
    }
}

function deleteAlbum(albumIndex) {
    if (!confirm('Are you sure you want to delete this album? All photos in this album will be deleted.')) {
        return;
    }

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
            location.reload();
        } else {
            alert(data.error || 'Failed to delete album');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the album');
    });
}

function addPhotosToAlbum(albumIndex) {
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
                location.reload();
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
}

function deletePhoto(albumIndex, photoIndex) {
    if (!confirm('Are you sure you want to delete this photo?')) {
        return;
    }

    // Remove photo from UI immediately for better UX
    const photoElement = document.querySelector(`[data-album-index="${albumIndex}"] [data-photo-index="${photoIndex}"]`);
    if (photoElement) {
        photoElement.style.opacity = '0.5';
        photoElement.style.pointerEvents = 'none';
    }

    // Use POST with _method=DELETE for better compatibility
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
            // Remove photo element from DOM
            if (photoElement) {
                photoElement.remove();
                // Update photo count
                const albumHeader = document.querySelector(`[data-album-index="${albumIndex}"]`);
                const photoCount = albumHeader.querySelectorAll('[data-photo-index]').length;
                const countSpan = albumHeader.querySelector('.text-sm.text-gray-500');
                if (countSpan) {
                    countSpan.textContent = photoCount + ' ' + (photoCount === 1 ? 'photo' : 'photos');
                }
            } else {
                location.reload();
            }
        } else {
            // Restore photo if deletion failed
            if (photoElement) {
                photoElement.style.opacity = '1';
                photoElement.style.pointerEvents = 'auto';
            }
            alert(data.error || 'Failed to delete photo');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restore photo if deletion failed
        if (photoElement) {
            photoElement.style.opacity = '1';
            photoElement.style.pointerEvents = 'auto';
        }
        const errorMsg = error.error || (typeof error === 'string' ? error : 'An error occurred while deleting the photo');
        alert(errorMsg);
    });
}

function openLightbox(imageSrc, caption) {
    document.getElementById('lightbox-image').src = imageSrc;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('lightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
        closeAddAlbumModal();
    }
});

// Initialize - first album open by default
document.addEventListener('DOMContentLoaded', function() {
    const firstIcon = document.getElementById('toggle-icon-0');
    if (firstIcon) {
        firstIcon.classList.add('rotate-180');
    }
});
</script>
@endpush
@endsection
