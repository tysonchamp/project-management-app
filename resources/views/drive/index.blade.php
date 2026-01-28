@extends('layouts.app')

@section('content')
    <div class="h-full flex flex-col bg-gray-50">
        <div class="p-6 pb-0">
            <!-- Header & Search -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Shared Drive</h1>
                    <!-- Breadcrumbs -->
                    <nav class="flex text-sm text-gray-500 mt-1">
                        <a href="{{ route('drive.index') }}" class="hover:text-indigo-600 hover:underline">Home</a>
                        @foreach ($breadcrumbs as $crumb)
                            <span class="mx-2">/</span>
                            <a href="{{ route('drive.index', ['folder_id' => $crumb->id]) }}"
                                class="hover:text-indigo-600 hover:underline">{{ $crumb->filename }}</a>
                        @endforeach
                    </nav>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    {{-- Search Form --}}
                    <form action="{{ route('drive.index') }}" method="GET" class="relative">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search files..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-64">
                        <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </form>

                    <button onclick="createFolder()"
                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 font-medium transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        New Folder
                    </button>

                    <button onclick="document.getElementById('drive-upload-input').click()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Upload File
                    </button>
                </div>
            </div>

            <!-- Drag & Drop Zone -->
            @if (!$search)
                <div id="drop-zone"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-white hover:bg-indigo-50 transition-colors cursor-pointer mb-8 relative"
                    ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)"
                    onclick="if(event.target === this || event.target.closest('.dz-message')) document.getElementById('drive-upload-input').click()">

                    <input type="file" id="drive-upload-input" class="hidden" onchange="handleFileSelect(this)">

                    <div class="dz-message pointer-events-none">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Drag and drop files here to upload to
                            <strong>{{ $currentFolder ? $currentFolder->filename : 'Home' }}</strong></p>
                        <p class="mt-1 text-xs text-gray-500">Maximum 100MB</p>
                    </div>

                    <!-- Upload Preview & Progress -->
                    <div id="upload-progress-container"
                        class="hidden absolute inset-0 bg-white bg-opacity-95 flex flex-col items-center justify-center rounded-lg z-10 p-6">
                        <div class="w-full max-w-md">
                            <div class="flex justify-between text-sm mb-2">
                                <span id="upload-filename" class="font-medium text-gray-700 truncate"></span>
                                <span id="upload-percentage" class="font-bold text-indigo-600">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <div class="mt-4">
                                <input type="text" id="upload-description" placeholder="Add a description (optional)"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="mt-4 flex justify-end space-x-2">
                                <button onclick="cancelUpload()"
                                    class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                                <button onclick="startUpload()"
                                    class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700">Start
                                    Upload</button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700">
                    Searching for: <strong>{{ $search }}</strong>. <a href="{{ route('drive.index') }}"
                        class="underline">Clear Search</a>
                </div>
            @endif
        </div>

        <!-- Files List -->
        <div class="flex-1 overflow-y-auto px-6 pb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($files as $file)
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow relative group">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 {{ $file->is_folder ? 'bg-yellow-50' : 'bg-indigo-50' }} rounded-lg cursor-pointer"
                                onclick="{{ $file->is_folder ? "window.location='" . route('drive.index', ['folder_id' => $file->id]) . "'" : '' }}">
                                @if ($file->is_folder)
                                    <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z">
                                        </path>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                @endif
                            </div>
                            @if (Auth::id() === $file->user_id)
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="deleteFile({{ $file->id }})"
                                        class="text-gray-400 hover:text-red-500 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <h3 class="font-medium text-gray-900 truncate mb-1 cursor-pointer hover:text-indigo-600"
                            title="{{ $file->filename }}"
                            onclick="{{ $file->is_folder ? "window.location='" . route('drive.index', ['folder_id' => $file->id]) . "'" : '' }}">
                            {{ $file->filename }}
                        </h3>
                        <p class="text-xs text-gray-500 mb-2">
                            @if ($file->is_folder)
                                Folder • Created by {{ $file->user->name }}
                            @else
                                {{ number_format($file->file_size / 1024, 1) }} KB • Uploaded by {{ $file->user->name }}
                            @endif
                        </p>

                        @if ($file->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 bg-gray-50 p-2 rounded text-xs">
                                {{ $file->description }}
                            </p>
                        @else
                            <div class="h-4 mb-4"></div>
                        @endif

                        @if (!$file->is_folder)
                            <div class="flex gap-2 mt-auto">
                                <button onclick="copyToClipboard('{{ Storage::url($file->file_path) }}')"
                                    class="flex-1 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-50 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                        </path>
                                    </svg>
                                    Copy
                                </button>
                                <a href="{{ Storage::url($file->file_path) }}" target="_blank" download
                                    class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($files->isEmpty())
                    <div class="col-span-full text-center py-12">
                        <div class="inline-block p-4 rounded-full bg-gray-100 mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No files found</h3>
                        <p class="text-gray-500 mt-1">Upload a file or create a folder to get started.</p>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $files->links() }}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        let selectedFile = null;
        const currentFolderId = "{{ request('folder_id') }}";

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('drop-zone').classList.add('bg-indigo-50', 'border-indigo-400');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('drop-zone').classList.remove('bg-indigo-50', 'border-indigo-400');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('drop-zone').classList.remove('bg-indigo-50', 'border-indigo-400');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                prepareUpload(e.dataTransfer.files[0]);
            }
        }

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                prepareUpload(input.files[0]);
            }
        }

        function prepareUpload(file) {
            selectedFile = file;
            document.getElementById('upload-filename').textContent = file.name;
            document.getElementById('upload-progress-container').classList.remove('hidden');
            document.getElementById('drive-upload-input').value = '';
        }

        function cancelUpload() {
            selectedFile = null;
            document.getElementById('upload-progress-container').classList.add('hidden');
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('upload-percentage').textContent = '0%';
            document.getElementById('upload-description').value = '';
        }

        async function startUpload() {
            if (!selectedFile) return;

            const description = document.getElementById('upload-description').value;
            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('description', description);
            if (currentFolderId) formData.append('parent_id', currentFolderId);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                await axios.post('{{ route('drive.store') }}', formData, {
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent
                            .total);
                        document.getElementById('progress-bar').style.width = percentCompleted + '%';
                        document.getElementById('upload-percentage').textContent = percentCompleted + '%';
                    }
                });
                cancelUpload();
                window.location.reload();
            } catch (error) {
                console.error('Upload failed:', error);
                alert('Upload failed. ' + (error.response?.data?.message || 'Check connection.'));
                cancelUpload();
            }
        }

        async function createFolder() {
            const name = prompt("Enter folder name:");
            if (!name) return;

            try {
                await axios.post('{{ route('drive.folder.create') }}', {
                    name: name,
                    parent_id: currentFolderId || null,
                    _token: '{{ csrf_token() }}'
                });
                window.location.reload();
            } catch (error) {
                alert('Failed to create folder.');
            }
        }

        async function deleteFile(id) {
            if (!confirm('Are you sure you want to delete this?')) return;
            try {
                await axios.post(`/drive/${id}`, {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                });
                window.location.reload();
            } catch (error) {
                console.error('Delete failed:', error);
                alert('Delete failed.');
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => alert('Link copied!')).catch(err => console.error(
                'Failed to copy', err));
        }
    </script>
@endsection
