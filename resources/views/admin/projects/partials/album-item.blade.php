@php
    use App\Helpers\StorageHelper;
@endphp
<div class="bg-white shadow-lg rounded-lg overflow-hidden" data-album-index="{{ $albumIndex }}">
    <!-- Album Header -->
    <div class="p-6 flex items-center justify-between">
        <button type="button" 
                onclick="toggleAlbum({{ $albumIndex }})" 
                class="flex-1 flex items-center justify-between hover:bg-gray-50 transition-colors text-left -m-6 p-6">
            <div class="flex items-center">
                <svg class="h-6 w-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 album-name">{{ $album['name'] ?? 'Album' }}</h3>
                    <span class="text-sm text-gray-500">{{ count($album['photos'] ?? []) }} {{ Str::plural('photo', count($album['photos'] ?? [])) }}</span>
                </div>
            </div>
            <svg id="toggle-icon-{{ $albumIndex }}" class="h-6 w-6 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        @if($isAdmin)
        <div class="flex items-center gap-2 ml-4">
            <button onclick="editAlbumName({{ $albumIndex }})" class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" title="Edit Album Name">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button onclick="addPhotosToAlbum({{ $albumIndex }})" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition" title="Add Photos">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button onclick="deleteAlbum({{ $albumIndex }})" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" title="Delete Album">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
        @endif
    </div>
    
    <!-- Album Content - Collapsible -->
    <div id="album-content-{{ $albumIndex }}" class="px-6 pb-6 {{ $albumIndex === 0 ? '' : 'hidden' }}">
        @if(isset($album['photos']) && is_array($album['photos']) && count($album['photos']) > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($album['photos'] as $photoIndex => $photo)
                    @php
                        $photoPath = $photo['path'] ?? '';
                        $photoName = $photo['original_name'] ?? 'Photo';
                        $photoUrl = StorageHelper::url($photoPath);
                        $fileExists = $photoPath ? StorageHelper::exists($photoPath) : false;
                    @endphp
                    @if($photoUrl)
                        <div class="relative group bg-white rounded-lg overflow-hidden shadow-md border border-gray-200 hover:shadow-lg transition-shadow" style="height: 160px;" data-photo-index="{{ $photoIndex }}">
                            <img
                                src="{{ $photoUrl }}"
                                alt="{{ $photoName }}"
                                class="w-full h-full cursor-pointer"
                                style="width: 100%; height: 160px; object-fit: cover; display: block;"
                                loading="lazy"
                                onclick="openLightbox('{{ $photoUrl }}', '{{ $photoName }}')"
                                onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27160%27%3E%3Crect width=%27200%27 height=%27160%27 fill=%27%23e5e7eb%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%239ca3af%27 font-size=%2712%27%3ENot Found%3C/text%3E%3C/svg%3E';">
                            @if($isAdmin)
                            <button onclick="deletePhoto({{ $albumIndex }}, {{ $photoIndex }})" 
                                    class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-lg opacity-90 hover:opacity-100 transition-opacity z-10" 
                                    title="Delete this photo">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-200 rounded-lg flex items-center justify-center" style="height: 160px;">
                            <p class="text-xs text-gray-500">No URL</p>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500 mb-4">No photos in this album</p>
                @if($isAdmin)
                <button onclick="addPhotosToAlbum({{ $albumIndex }})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Add Photos
                </button>
                @endif
            </div>
        @endif
    </div>
</div>

