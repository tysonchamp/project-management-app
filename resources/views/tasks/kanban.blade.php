@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tasks - Kanban Board</h1>
        <div class="flex space-x-2">
            <a href="{{ route('tasks.index') }}"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">List View</a>
            <a href="{{ route('tasks.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Create New Task</a>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6 overflow-x-auto pb-4">
        <!-- To Do Column -->
        <div class="flex-1 min-w-[300px]">
            <div class="bg-gray-100 rounded-lg p-4 h-full">
                <h3 class="font-bold text-gray-700 mb-4 flex items-center justify-between">
                    To Do
                    <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded-full text-xs">{{ $todoTasks->count() }}</span>
                </h3>
                <div class="space-y-4">
                    @foreach ($todoTasks as $task)
                        @include('tasks.partials.kanban-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="flex-1 min-w-[300px]">
            <div class="bg-blue-50 rounded-lg p-4 h-full border border-blue-100">
                <h3 class="font-bold text-blue-700 mb-4 flex items-center justify-between">
                    In Progress
                    <span
                        class="bg-blue-200 text-blue-600 px-2 py-1 rounded-full text-xs">{{ $inProgressTasks->count() }}</span>
                </h3>
                <div class="space-y-4">
                    @foreach ($inProgressTasks as $task)
                        @include('tasks.partials.kanban-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Done Column -->
        <div class="flex-1 min-w-[300px]">
            <div class="bg-green-50 rounded-lg p-4 h-full border border-green-100">
                <h3 class="font-bold text-green-700 mb-4 flex items-center justify-between">
                    Done
                    <span
                        class="bg-green-200 text-green-600 px-2 py-1 rounded-full text-xs">{{ $doneTasks->count() }}</span>
                </h3>
                <div class="space-y-4">
                    @foreach ($doneTasks as $task)
                        @include('tasks.partials.kanban-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
