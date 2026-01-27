<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Display a listing of personal and shared notes.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Fetch owned notes
        $ownedNotes = $user->ownedNotes()->latest()->get();
        
        // Fetch shared notes
        $sharedNotes = $user->sharedNotes()->latest()->get();

        $allUsers = User::where('id', '!=', $user->id)->get(); // For sharing modal

        return view('notes.index', compact('ownedNotes', 'sharedNotes', 'allUsers'));
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string',
        ]);
        
        // Ensure at least title or content is present
        if (empty($validated['title']) && empty($validated['content'])) {
            return back()->with('error', 'Note cannot be empty.');
        }

        Auth::user()->ownedNotes()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'color' => $validated['color'] ?? 'bg-white',
        ]);

        return back()->with('success', 'Note created.');
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, Note $note)
    {
        // Check permission: Owner or Shared with edit permission
        $user = Auth::user();
        $isOwner = $note->user_id === $user->id;
        $isShared = $note->sharedWith()->where('user_id', $user->id)->where('can_edit', true)->exists();

        if (!$isOwner && !$isShared) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $note->update($validated);

        return back()->with('success', 'Note updated.');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            // If shared user tries to delete, maybe remote themselves?
            // For now, restrict to owner.
            abort(403, 'Unauthorized');
        }

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }

    /**
     * Share note with users.
     */
    public function share(Request $request, Note $note)
    {
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        if (isset($validated['users'])) {
            // Sync users (default can_edit = true)
            $note->sharedWith()->sync($validated['users']);
        } else {
            $note->sharedWith()->detach();
        }

        return back()->with('success', 'Sharing updated.');
    }
}
