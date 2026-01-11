@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Project Credentials</h1>
        @if (Auth::user()->role === 'admin')
            <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                Add Credential
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (Auth::user()->role === 'admin')
        <div class="bg-white p-4 rounded-t-lg shadow border-b flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <span id="selected-count">0</span> credentials selected
            </div>
            <div class="flex space-x-2">
                <button onclick="openShareModal()" id="bulk-share-btn" disabled
                    class="bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Share Selected
                </button>
                <form action="{{ route('credentials.bulkDestroy') }}" method="POST"
                    onsubmit="return confirm('Delete selected credentials?')" class="inline">
                    @csrf
                    <input type="hidden" name="credential_ids" id="bulk-delete-ids">
                    <button type="submit" id="bulk-delete-btn" disabled
                        class="bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        Delete Selected
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden @if (Auth::user()->role === 'admin') rounded-t-none @endif">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @if (Auth::user()->role === 'admin')
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                            <input type="checkbox" id="select-all"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project /
                        Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                    @if (Auth::user()->role === 'admin')
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Access
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($credentials as $cred)
                    <tr>
                        @if (Auth::user()->role === 'admin')
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_creds[]" value="{{ $cred->id }}"
                                    class="cred-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </td>
                        @endif
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $cred->project_name }}</div>
                            <div class="text-sm text-gray-500">{{ $cred->service_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><span class="font-semibold">User:</span>
                                {{ $cred->username ?? 'N/A' }}</div>
                            @if ($cred->description)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ Illuminate\Support\Str::limit($cred->description, 30) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <input type="password" value="{{ $cred->password }}" readonly
                                    class="text-sm text-gray-600 bg-gray-50 border-none rounded p-1 w-32 focus:ring-0"
                                    id="pwd-{{ $cred->id }}">
                                <button type="button" onclick="togglePassword({{ $cred->id }})"
                                    class="text-gray-400 hover:text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        id="eye-{{ $cred->id }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                                <button type="button" onclick="copyToClipboard('{{ $cred->password }}')"
                                    class="text-gray-400 hover:text-green-600" title="Copy">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @if (Auth::user()->role === 'admin')
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 cursor-pointer"
                                    title="{{ $cred->accessList->pluck('name')->join(', ') }}">
                                    {{ $cred->accessList->count() }} Users
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('credentials.destroy', $cred) }}" method="POST"
                                    onsubmit="return confirm('Delete this credential?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No credentials found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Credential</h3>
                <form action="{{ route('credentials.store') }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Project Name</label>
                        <input type="text" name="project_name" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Service Name</label>
                        <input type="text" name="service_name" required placeholder="e.g. AWS, Database"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                        <input type="text" name="username"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="text" name="password" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea name="description"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Share Credentials</h3>
            <p class="text-sm text-gray-500 mb-4">Select users to grant access to the selected credentials.</p>
            <form action="{{ route('credentials.share') }}" method="POST">
                @csrf
                <div id="share-credential-inputs"></div>

                <div class="max-h-60 overflow-y-auto border rounded p-2 mb-4">
                    @foreach ($users as $user)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                id="user-{{ $user->id }}" class="mr-2 rounded border-gray-300">
                            <label for="user-{{ $user->id }}" class="text-sm text-gray-700">{{ $user->name }}
                                ({{ $user->role }})</label>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('shareModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update
                        Access</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById('pwd-' + id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Optional: Show toast
            });
        }

        // Bulk Actions Logic
        const checkboxes = document.querySelectorAll('.cred-checkbox');
        const selectAll = document.getElementById('select-all');
        const selectedCount = document.getElementById('selected-count');
        const shareBtn = document.getElementById('bulk-share-btn');
        const deleteBtn = document.getElementById('bulk-delete-btn');
        const deleteInput = document.getElementById('bulk-delete-ids');
        const shareInputsContainer = document.getElementById('share-credential-inputs');

        function updateBulkUI() {
            const selected = Array.from(checkboxes).filter(cb => cb.checked);
            const count = selected.length;

            if (selectedCount) selectedCount.innerText = count;
            if (shareBtn) shareBtn.disabled = count === 0;
            if (deleteBtn) deleteBtn.disabled = count === 0;

            // Populate delete input
            if (deleteInput) deleteInput.value = JSON.stringify(selected.map(cb => cb
            .value)); // Controller expects simple array, we might need to adjust or let PHP parse array inputs if form is array

            // Populate share inputs
            if (shareInputsContainer) {
                shareInputsContainer.innerHTML = '';
                selected.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'credential_ids[]';
                    input.value = cb.value;
                    shareInputsContainer.appendChild(input);
                });
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkUI();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkUI);
        });

        function openShareModal() {
            document.getElementById('shareModal').classList.remove('hidden');
        }

        // Initial update
        if (checkboxes.length > 0) updateBulkUI();
    </script>
@endsection
