<div
    class="break-inside-avoid mb-4 group relative rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow {{ $note->color }}">
    <div class="p-4 cursor-pointer"
        onclick="openEditModal({{ $note->id }}, {{ json_encode($note->title) }}, {{ json_encode($note->content) }}, '{{ $note->color }}')">
        @if ($note->title)
            <h4 class="font-bold text-gray-900 mb-2">{{ $note->title }}</h4>
        @endif
        <p class="text-gray-700 whitespace-pre-wrap text-sm">{{ $note->content }}</p>
    </div>

    <div class="px-4 py-2 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
        <div class="flex space-x-2">
            @if (!$isShared)
                <button type="button" class="text-gray-500 hover:text-gray-900" title="Collaborator"
                    onclick="openShareModal({{ $note->id }}, {{ json_encode($note->sharedWith->pluck('id')) }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </button>
            @endif
            @if ($isShared)
                <span class="text-xs text-gray-500" title="Shared by {{ $note->owner->name }}">
                    Start by {{ substr($note->owner->name, 0, 1) }}
                </span>
            @endif
        </div>

        @if (!$isShared || ($isShared && $note->pivot->can_edit))
            <div class="flex space-x-2">
                @if (!$isShared)
                    <form action="{{ route('notes.destroy', $note) }}" method="POST"
                        onsubmit="return confirm('Delete note?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
