@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Create New User</a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left font-medium text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="py-3 px-6 text-left font-medium text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="py-3 px-6 text-left font-medium text-gray-600 uppercase tracking-wider">Role</th>
                    <th class="py-3 px-6 text-left font-medium text-gray-600 uppercase tracking-wider">Joined</th>
                    <th class="py-3 px-6 text-right font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6 text-gray-800">{{ $user->name }}</td>
                        <td class="py-4 px-6 text-gray-600">{{ $user->email }}</td>
                        <td class="py-4 px-6">
                            <span
                                class="px-2 py-1 rounded text-xs font-semibold
                            @if ($user->role === 'admin') bg-red-100 text-red-800
                            @elseif($user->role === 'project_manager') bg-purple-100 text-purple-800
                            @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                            @if (auth()->id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 font-medium ml-2">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
