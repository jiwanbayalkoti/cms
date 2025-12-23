@extends('admin.layout')

@section('title', 'Create Project')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create New Project</h1>
    <p class="mt-2 text-gray-600">Define scope, budget, and schedule for the initiative</p>
</div>

<div class="bg-white shadow-lg rounded-lg p-6">
    <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data"
          data-validate="true"
          data-validation-route="{{ route('admin.projects.validate') }}"
          id="projectForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client / Stakeholder</label>
                <input type="text" name="client_name" id="client_name" value="{{ old('client_name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('client_name') border-red-500 @enderror">
                @error('client_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ old('status', 'planned') === $status ? 'selected' : '' }}>
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
                <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Budget (optional)</label>
                <input type="number" step="0.01" name="budget" id="budget" value="{{ old('budget') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('budget') border-red-500 @enderror">
                @error('budget')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" id="description" rows="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
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
                <!-- File fields will be added here dynamically -->
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
                <!-- Album fields will be added here dynamically -->
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">Create Project</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let fileFieldIndex = 0;

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
                    ${isExisting ? `
                        <div class="flex items-center gap-2">
                            <a href="/storage/${filePath}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                View Current File
                            </a>
                            <input type="hidden" name="existing_files[]" value="${filePath}">
                        </div>
                        <input type="file" name="files[]" class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current file, or upload a new one</p>
                    ` : `
                        <input type="file" name="files[]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                    `}
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

let albumFieldIndex = 0;

function addAlbumField(albumName = '', existingPhotos = [], existingAlbumIndex = null) {
    const container = document.getElementById('albumFieldsContainer');
    const fieldId = `album-field-${albumFieldIndex++}`;
    const albumIndex = container.children.length;
    
    let photosHtml = '';
    if (existingPhotos.length > 0) {
        existingPhotos.forEach((photo, photoIndex) => {
            photosHtml += `
                <div class="relative group">
                    <img src="{{ asset('storage') }}/${photo.path}" alt="${photo.original_name}" class="w-full h-32 object-cover rounded-lg">
                    <input type="hidden" name="existing_album_indices[${albumIndex}]" value="${existingAlbumIndex}">
                    <input type="hidden" name="existing_photos[${albumIndex}][${photoIndex}]" value="${photo.path}">
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
                <h3 class="text-lg font-semibold text-gray-900">Album ${albumFieldIndex + 1}</h3>
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
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
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

// Add initial file field on page load
document.addEventListener('DOMContentLoaded', function() {
    addFileField();
});
</script>
@endpush
@endsection

