@php
    use App\Helpers\StorageHelper;
    use App\Models\User;
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
                    <h3 class="text-base md:text-lg lg:text-xl font-semibold text-gray-900 album-name">{{ $album['name'] ?? 'Album' }}</h3>
                    <span class="text-sm text-gray-500">{{ count($album['photos'] ?? []) }} {{ Str::plural('photo', count($album['photos'] ?? [])) }}</span>
                </div>
            </div>
            <svg id="toggle-icon-{{ $albumIndex }}" class="h-6 w-6 text-gray-400 transform transition-transform {{ ($isFirst ?? ($albumIndex === 0)) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <button onclick="showDeleteAlbumConfirmation({{ $albumIndex }}, '{{ addslashes($album['name'] ?? 'Album') }}')" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition" title="Delete Album">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
        @endif
    </div>
    
    <!-- Album Content - Collapsible -->
    <div id="album-content-{{ $albumIndex }}" class="px-6 pb-6 {{ ($isFirst ?? ($albumIndex === 0)) ? '' : 'hidden' }}">
        @if(isset($album['photos']) && is_array($album['photos']) && count($album['photos']) > 0)
            @if(isset($isSiteEngineer) && $isSiteEngineer)
            <!-- Bulk Approval Controls for Site Engineers -->
            <div class="mb-4 flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="select-all-{{ $albumIndex }}" onchange="toggleSelectAll({{ $albumIndex }})" class="rounded border-gray-300">
                    <label for="select-all-{{ $albumIndex }}" class="text-sm font-medium text-gray-700">Select All</label>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="bulkApprovePhotos({{ $albumIndex }}, 'approve')" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve Selected
                    </button>
                    <button onclick="bulkApprovePhotos({{ $albumIndex }}, 'disapprove')" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Disapprove Selected
                    </button>
                </div>
            </div>
            @endif
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($album['photos'] as $photoIndex => $photo)
                    @php
                        $photoPath = $photo['path'] ?? '';
                        $photoName = $photo['original_name'] ?? 'Photo';
                        $photoUrl = StorageHelper::url($photoPath);
                        $fileExists = $photoPath ? StorageHelper::exists($photoPath) : false;
                        $approvalStatus = $photo['approval_status'] ?? 'pending';
                        $approvedBy = $photo['approved_by'] ?? null;
                        $approvedAt = $photo['approved_at'] ?? null;
                        $approverName = null;
                        if ($approvedBy) {
                            $approver = User::find($approvedBy);
                            $approverName = $approver ? $approver->name : 'Unknown';
                        }
                    @endphp
                    @if($photoUrl)
                        <div class="relative group bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow" data-photo-index="{{ $photoIndex }}" style="overflow: visible;">
                            @if(isset($isSiteEngineer) && $isSiteEngineer && $approvalStatus === 'pending')
                            <!-- Checkbox for bulk selection (only for pending photos) -->
                            <input type="checkbox" 
                                   class="photo-checkbox absolute top-2 left-2 z-20 rounded border-gray-300 bg-white shadow-lg" 
                                   data-album-index="{{ $albumIndex }}" 
                                   data-photo-index="{{ $photoIndex }}"
                                   onchange="updateBulkButtons({{ $albumIndex }})">
                            @endif
                            <div class="relative overflow-hidden rounded-t-lg" style="height: 160px;">
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="{{ $photoName }}"
                                    class="w-full h-full cursor-pointer"
                                    style="width: 100%; height: 160px; object-fit: cover; display: block;"
                                    loading="lazy"
                                    decoding="async"
                                    data-photo-url="{{ $photoUrl }}"
                                    data-photo-caption="{{ $photoName }}"
                                    onclick="openLightbox('{{ $photoUrl }}', '{{ $photoName }}', {{ $albumIndex }}, {{ $photoIndex }})"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27160%27%3E%3Crect width=%27200%27 height=%27160%27 fill=%27%23e5e7eb%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%239ca3af%27 font-size=%2712%27%3ENot Found%3C/text%3E%3C/svg%3E';">
                            </div>
                            
                            <!-- Status and Approver Info (below image) -->
                            <div class="p-2 bg-gray-50 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-1 gap-2 min-w-0">
                                    <span class="text-xs font-medium text-gray-700 flex-shrink-0 status-label">Status:</span>
                                    @if($approvalStatus === 'approved')
                                        <span class="bg-green-600 text-white text-xs px-2 py-0.5 rounded whitespace-nowrap flex-shrink-0 status-badge">
                                            <span class="status-text">Approved</span>
                                            <svg class="h-3 w-3 inline status-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </span>
                                    @elseif($approvalStatus === 'disapproved')
                                        <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded whitespace-nowrap flex-shrink-0 status-badge">
                                            <span class="status-text">Disapproved</span>
                                            <svg class="h-3 w-3 inline status-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded whitespace-nowrap flex-shrink-0 status-badge">
                                            <span class="status-text">Pending</span>
                                            <svg class="h-3 w-3 inline status-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                                @if($approverName && $approvedAt)
                                    <div class="text-xs text-gray-600 mt-1 break-words approver-info">
                                        <span class="font-medium approver-label">By:</span> <span class="break-all approver-name">{{ $approverName }}</span>
                                        <span class="text-gray-400 ml-1 whitespace-nowrap approver-date">({{ \Carbon\Carbon::parse($approvedAt)->format('M d, Y') }})</span>
                                    </div>
                                @endif
                            </div>
                            
                            @if(isset($isSiteEngineer) && $isSiteEngineer && $approvalStatus === 'pending')
                            <!-- Approval buttons for site engineers (only for pending photos) -->
                            <div class="absolute bottom-2 left-2 right-2 flex gap-1 z-10" style="bottom: 2px;">
                                <button onclick="event.stopPropagation(); approvePhoto({{ $albumIndex }}, {{ $photoIndex }})" 
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs py-1.5 px-2 rounded shadow-lg opacity-90 hover:opacity-100 transition-opacity" 
                                        title="Approve">
                                    <svg class="h-3 w-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <button onclick="event.stopPropagation(); disapprovePhoto({{ $albumIndex }}, {{ $photoIndex }})" 
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs py-1.5 px-2 rounded shadow-lg opacity-90 hover:opacity-100 transition-opacity" 
                                        title="Disapprove">
                                    <svg class="h-3 w-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            @endif
                            
                            @if($isAdmin && $approvalStatus !== 'approved')
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

