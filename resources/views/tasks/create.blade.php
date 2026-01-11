@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Create New Task</h2>

        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                <input type="text" name="title" id="title" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    placeholder="e.g. Implement Authorization">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="4" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                    placeholder="Describe the task details..."></textarea>
            </div>

            <div class="mb-6">
                <label for="assignees" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                <p class="text-xs text-gray-500 mb-2">Hold Cmd/Ctrl to select multiple users</p>
                <select name="assignees[]" id="assignees" multiple required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border h-40">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('tasks.index') }}"
                    class="mr-4 text-gray-600 hover:text-gray-800 font-medium py-2">Cancel</a>
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium transition">Create
                    Task</button>
            </div>
        </form>
    </div>
@endsection
