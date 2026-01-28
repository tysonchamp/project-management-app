@extends('layouts.app')

@section('content')
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b pb-4 md:border-none md:pb-0">
        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Tasks <span class="text-gray-400 font-normal">/</span>
            Kanban</h1>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
            <a href="{{ route('tasks.index') }}"
                class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-center shadow-sm text-sm font-medium w-full sm:w-auto">
                <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
                List View
            </a>
            <a href="{{ route('tasks.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-center shadow-sm text-sm font-medium w-full sm:w-auto">
                Create New Task
            </a>
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
