@extends('admin.layout')

@section('title', 'Project Gallery - ' . $project->name)

@section('content')
@php
    use App\Support\CompanyContext;
    use Illuminate\Support\Facades\Storage;

    $activeCompanyId = CompanyContext::getActiveCompanyId();
@endphp

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }} - Photo Gallery</h1>
        <p class="text-gray-600 mt-2">{{ $project->client_name ?? 'Project Gallery' }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.projects.show', $project) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
            View Project Details
        </a>
        <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
            Edit Project
        </a>
    </div>
</div>

@if($project->photos && is_array($project->photos) && count($project->photos) > 0)
    <div class="space-y-4">
        @foreach($project->photos as $albumIndex => $album)
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Album Header - Toggle Button -->
                <button type="button" 
                        onclick="toggleAlbum({{ $albumIndex }})" 
                        class="w-full p-6 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $album['name'] ?? 'Album' }}</h3>
                            <span class="text-sm text-gray-500">{{ count($album['photos'] ?? []) }} {{ Str::plural('photo', count($album['photos'] ?? [])) }}</span>
                        </div>
                    </div>
                    <svg id="toggle-icon-{{ $albumIndex }}" class="h-6 w-6 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- Album Content - Collapsible -->
                <div id="album-content-{{ $albumIndex }}" class="px-6 pb-6 {{ $albumIndex === 0 ? '' : 'hidden' }}">
                    @if(isset($album['photos']) && is_array($album['photos']) && count($album['photos']) > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($album['photos'] as $photoIndex => $photo)
                                @php
                                    $photoPath = $photo['path'] ?? '';
                                    $photoName = $photo['original_name'] ?? 'Photo';
                                    
                                    // Generate URL - paths are like "projects/photos/filename.jpg"
                                    $photoUrl = '';
                                    if ($photoPath) {
                                        // Clean the path and build full absolute URL using current host
                                        $photoPath = trim($photoPath, '/');
                                        $base = request()->getSchemeAndHttpHost();
                                        $photoUrl = $base . '/storage/' . $photoPath;
                                    }
                                @endphp
                                @if($photoUrl)
                                    <div class="bg-white rounded-lg overflow-hidden shadow-md border border-gray-200 hover:shadow-lg transition-shadow" style="height: 160px;">
                                        <img
                                            src="{{ $photoUrl }}"
                                            alt="{{ $photoName }}"
                                            class="w-full h-full"
                                            style="width: 100%; height: 160px; object-fit: cover; display: block; cursor: pointer;"
                                            loading="lazy"
                                            onclick="openLightbox('{{ $photoUrl }}', '{{ $photoName }}')"
                                            onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27160%27%3E%3Crect width=%27200%27 height=%27160%27 fill=%27%23e5e7eb%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%239ca3af%27 font-size=%2712%27%3ENot Found%3C/text%3E%3C/svg%3E';">
                                    </div>
                                @else
                                    <div class="bg-gray-200 rounded-lg flex items-center justify-center" style="height: 160px;">
                                        <p class="text-xs text-gray-500">No URL</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No photos in this album</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white shadow-lg rounded-lg p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No photos found</h3>
        <p class="mt-2 text-sm text-gray-500">This project doesn't have any photo albums yet.</p>
        <div class="mt-6">
            <a href="{{ route('admin.projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Photo Album
            </a>
        </div>
    </div>
@endif

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

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
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

