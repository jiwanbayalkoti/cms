@extends('admin.layout')

@section('title', 'Edit Project')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Project</h1>
    <p class="mt-2 text-gray-600">Update scope, timing, or budget details</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data"
          data-validate="true"
          data-validation-route="{{ route('admin.projects.validate') }}"
          id="projectForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client / Stakeholder</label>
                <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $project->client_name) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('client_name') border-red-500 @enderror">
                @error('client_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ old('status', $project->status) === $status ? 'selected' : '' }}>
                            {{ Str::headline($status) }}
                        </option>
                    @endforeach
                </select>
                <div class="field-error text-red-600 text-sm mt-1" data-field="status" style="display: none;"></div>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Budget</label>
                <input type="number" step="0.01" name="budget" id="budget" value="{{ old('budget', $project->budget) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('budget') border-red-500 @enderror">
                @error('budget')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" id="description" rows="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $project->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Files Section -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">Project Files</label>
                <button type="button" onclick="addFileField()" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add File
                </button>
            </div>
            <div id="fileFieldsContainer" class="space-y-4">
                @if($project->files && count($project->files) > 0)
                    @foreach($project->files as $index => $file)
                        <div id="existing-file-{{ $index }}" class="flex items-center gap-4 p-4 border border-gray-300 rounded-lg bg-gray-50">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">File Name</label>
                                    <input type="text" name="file_names[]" value="{{ $file['name'] ?? '' }}" placeholder="e.g., Agreement, Bid Doc, BOQ File" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <input type="hidden" name="existing_file_indices[]" value="{{ $index }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                                    <div class="mb-2">
                                        <a href="{{ \App\Helpers\StorageHelper::url($file['path'] ?? '') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center mb-2">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            View Current File
                                        </a>
                                    </div>
                                    <input type="file" name="files[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current file, or upload a new one</p>
                                    <label class="flex items-center mt-2">
                                        <input type="checkbox" name="delete_files[]" value="{{ $index }}" class="mr-2">
                                        <span class="text-sm text-red-600">Delete this file</span>
                                    </label>
                                </div>
                            </div>
                            <button type="button" onclick="removeFileField('existing-file-{{ $index }}')" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Photo Gallery Section -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-gray-700">Photo Gallery</label>
                <button type="button" onclick="addAlbumField()" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition duration-200 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Album
                </button>
            </div>
            <div id="albumFieldsContainer" class="space-y-6">
                @if($project->photos && count($project->photos) > 0)
                    @foreach($project->photos as $index => $album)
                        <div id="existing-album-{{ $index }}" class="border border-gray-300 rounded-lg p-6 bg-gray-50">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Album {{ $index + 1 }}</h3>
                                <button type="button" onclick="removeAlbumField('existing-album-{{ $index }}')" class="px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                                <input type="text" name="album_names[]" value="{{ $album['name'] ?? '' }}" placeholder="e.g., Foundation, Beam, DBC" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                <input type="hidden" name="existing_album_indices[]" value="{{ $index }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Photos (Multiple selection allowed)</label>
                                <input type="file" name="album_photos[{{ $index }}][]" multiple accept="image/*" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                    onchange="previewAlbumPhotos(this, 'existing-album-{{ $index }}')">
                                <div id="preview-existing-album-{{ $index }}" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @if(isset($album['photos']) && count($album['photos']) > 0)
                                        @foreach($album['photos'] as $photoIndex => $photo)
                                            <div class="relative group">
                                                @php
                                                    $photoPath = $photo['path'] ?? '';
                                                    // Use StorageHelper for consistent URL generation
                                                    $photoUrl = \App\Helpers\StorageHelper::url($photoPath);
                                                @endphp
                                                <img src="{{ $photoUrl }}" alt="{{ $photo['original_name'] ?? '' }}" class="w-full h-32 object-cover rounded-lg">
                                                <input type="hidden" name="existing_photos[{{ $index }}][{{ $photoIndex }}]" value="{{ $photo['path'] ?? '' }}">
                                                <button type="button" onclick="removeExistingPhoto(this, '{{ $index }}', '{{ $photoIndex }}')" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">Save Changes</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let fileFieldIndex = {{ $project->files ? count($project->files) : 0 }};

function addFileField(fileName = '', filePath = '', isExisting = false) {
    const container = document.getElementById('fileFieldsContainer');
    const fieldId = `file-field-${fileFieldIndex++}`;
    
    const fileFieldHtml = `
        <div id="${fieldId}" class="flex items-center gap-4 p-4 border border-gray-300 rounded-lg bg-gray-50">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Name</label>
                    <input type="text" name="file_names[]" value="${fileName}" placeholder="e.g., Agreement, Bid Doc, BOQ File" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                    <input type="file" name="files[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="button" onclick="removeFileField('${fieldId}')" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fileFieldHtml);
}

function removeFileField(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.remove();
    }
}

let albumFieldIndex = {{ $project->photos ? count($project->photos) : 0 }};

function addAlbumField(albumName = '', existingPhotos = [], existingAlbumIndex = null) {
    const container = document.getElementById('albumFieldsContainer');
    const fieldId = `album-field-${albumFieldIndex++}`;
    const albumIndex = container.children.length;
    
    let photosHtml = '';
    if (existingPhotos && existingPhotos.length > 0) {
        existingPhotos.forEach((photo, photoIndex) => {
            const photoPath = photo.path || photo;
            const photoName = photo.original_name || photo.name || '';
            photosHtml += `
                <div class="relative group">
                    <img src="/storage/${photoPath}" alt="${photoName}" class="w-full h-32 object-cover rounded-lg">
                    <input type="hidden" name="existing_photos[${albumIndex}][${photoIndex}]" value="${photoPath}">
                    <button type="button" onclick="removeExistingPhoto(this, '${albumIndex}', '${photoIndex}')" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
        });
    }
    
    const albumFieldHtml = `
        <div id="${fieldId}" class="border border-gray-300 rounded-lg p-6 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Album ${albumIndex + 1}</h3>
                <button type="button" onclick="removeAlbumField('${fieldId}')" class="px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                <input type="text" name="album_names[]" value="${albumName}" placeholder="e.g., Foundation, Beam, DBC" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                ${existingAlbumIndex !== null ? `<input type="hidden" name="existing_album_indices[${albumIndex}]" value="${existingAlbumIndex}">` : ''}
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Photos (Multiple selection allowed)</label>
                <input type="file" name="album_photos[${albumIndex}][]" multiple accept="image/*" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    onchange="previewAlbumPhotos(this, '${fieldId}')">
                <div id="preview-${fieldId}" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                    ${photosHtml}
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', albumFieldHtml);
}

function removeAlbumField(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.remove();
    }
}

function previewAlbumPhotos(input, fieldId) {
    const preview = document.getElementById(`preview-${fieldId}`);
    const existingPhotos = preview.querySelectorAll('.relative.group');
    
    if (input.files) {
        Array.from(input.files).forEach((file) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}" class="w-full h-32 object-cover rounded-lg">
                        <button type="button" onclick="removePhotoPreview(this)" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function removePhotoPreview(button) {
    button.closest('.relative').remove();
}

function removeExistingPhoto(button, albumIndex, photoIndex) {
    const photoDiv = button.closest('.relative');
    // Add delete flag
    const deleteInput = document.createElement('input');
    deleteInput.type = 'hidden';
    deleteInput.name = 'delete_photos[]';
    deleteInput.value = `${albumIndex}-${photoIndex}`;
    photoDiv.parentElement.appendChild(deleteInput);
    photoDiv.remove();
}
</script>
@endpush
@endsection

