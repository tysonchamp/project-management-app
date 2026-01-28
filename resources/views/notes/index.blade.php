@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Create Note Section (Google Keep Style) -->
        <div class="max-w-2xl mx-auto mb-8">
            <div class="bg-white shadow rounded-lg p-4 transition-all duration-200" id="create-note-container">
                <form action="{{ route('notes.store') }}" method="POST">
                    @csrf
                    <input type="text" name="title" placeholder="Title"
                        class="w-full text-lg font-bold border-none focus:ring-0 p-0 mb-2 placeholder-gray-500 bg-transparent text-gray-900"
                        style="display:none;" id="create-title">
                    <textarea name="content" placeholder="Take a note..." rows="1"
                        class="w-full resize-none border-none focus:ring-0 p-0 text-gray-700 placeholder-gray-500 bg-transparent"
                        id="create-content"></textarea>

                    <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-100" style="display:none;"
                        id="create-actions">
                        <div class="flex space-x-2">
                            <!-- Color Picker (Hidden Input + UI) -->
                            <input type="hidden" name="color" id="create-color" value="bg-white">
                            <div class="flex space-x-1">
                                @foreach (['bg-white', 'bg-red-100', 'bg-yellow-100', 'bg-green-100', 'bg-blue-100', 'bg-purple-100'] as $color)
                                    <button type="button"
                                        class="w-5 h-5 rounded-full border border-gray-200 {{ $color }}"
                                        onclick="document.getElementById('create-color').value='{{ $color }}'; document.getElementById('create-note-container').className = '{{ $color }} shadow rounded-lg p-4 transition-all duration-200';"></button>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit"
                            class="text-sm font-medium text-gray-900 hover:bg-gray-100 px-4 py-2 rounded">Close</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notes Grid -->
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">My Notes</h3>
        @if ($ownedNotes->count() > 0 || $sharedNotes->count() > 0)
            <div class="columns-1 md:columns-2 lg:columns-3 gap-4 space-y-4">

                @foreach ($ownedNotes as $note)
                    @include('notes.partials.card', ['note' => $note, 'isShared' => false])
                @endforeach

                @foreach ($sharedNotes as $note)
                    @include('notes.partials.card', ['note' => $note, 'isShared' => true])
                @endforeach

            </div>
        @else
            <div class="text-center py-10 text-gray-500">
                <p>No notes yet. Capture your ideas!</p>
            </div>
        @endif

    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div id="edit-modal-content"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="share-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="document.getElementById('share-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form id="share-form" method="POST">
                    @csrf
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Collaborators</h3>
                    <div class="space-y-2 max-h-60 overflow-y-auto mb-4">
                        @foreach ($allUsers as $user)
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                    class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300">
                                <span class="text-gray-700">{{ $user->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-5 sm:mt-6 flex justify-end space-x-2">
                        <button type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm"
                            onclick="document.getElementById('share-modal').classList.add('hidden')">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:text-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactions -->
    <script>
        // Create Note Expansion
        const createContainer = document.getElementById('create-note-container');
        const createTitle = document.getElementById('create-title');
        const createContent = document.getElementById('create-content');
        const createActions = document.getElementById('create-actions');

        createContent.addEventListener('focus', () => {
            createTitle.style.display = 'block';
            createActions.style.display = 'flex';
            createContent.rows = 3;
        });

        // Edit Modal Logic
        function openEditModal(noteId, title, content, color) {
            const modal = document.getElementById('edit-modal');
            const container = document.getElementById('edit-modal-content');

            // Set form action dynamically
            const action = `/notes/${noteId}`;

            container.innerHTML = `
                <form action="${action}" method="POST" class="${color} p-4">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="${title}" placeholder="Title" class="w-full text-lg font-bold border-none focus:ring-0 p-0 mb-2 bg-transparent text-gray-900">
                    <textarea name="content" placeholder="Note" rows="5" class="w-full resize-none border-none focus:ring-0 p-0 bg-transparent text-gray-700">${content}</textarea>
                    
                    <div class="flex justify-between items-center mt-3 pt-2">
                        <select name="color" onchange="this.form.className = this.value + ' p-4'" class="text-xs border-none bg-transparent">
                            <option value="bg-white" ${color == 'bg-white' ? 'selected' : ''}>White</option>
                            <option value="bg-red-100" ${color == 'bg-red-100' ? 'selected' : ''}>Red</option>
                            <option value="bg-yellow-100" ${color == 'bg-yellow-100' ? 'selected' : ''}>Yellow</option>
                            <option value="bg-green-100" ${color == 'bg-green-100' ? 'selected' : ''}>Green</option>
                            <option value="bg-blue-100" ${color == 'bg-blue-100' ? 'selected' : ''}>Blue</option>
                            <option value="bg-purple-100" ${color == 'bg-purple-100' ? 'selected' : ''}>Purple</option>
                        </select>
                        <button type="submit" class="text-sm font-medium text-gray-900 hover:bg-black/10 px-4 py-2 rounded">Close</button>
                    </div>
                </form>
            `;

            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        // Share Modal Logic
        function openShareModal(noteId, sharedUserIds) {
            const modal = document.getElementById('share-modal');
            const form = document.getElementById('share-form');
            form.action = `/notes/${noteId}/share`;

            // Reset checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = sharedUserIds.includes(parseInt(cb.value));
            });

            modal.classList.remove('hidden');
        }
    </script>
@endsection
