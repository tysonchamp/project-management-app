<?php

namespace App\Http\Controllers;

use App\Models\TaskUpdates; // Note: Model file is TaskUpdates.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskUpdateController extends Controller
{
    /**
     * Store a newly created task update in storage.
     */
    public function store(Request $request, $taskId)
    {
        $validated = $request->validate([
            'update' => ['required', 'string'],
        ]);

        try {
            TaskUpdates::create([
                'task_id' => $taskId,
                'user_id' => Auth::id() ?? 1,
                'update' => $validated['update'],
            ]);

            return back()->with('success', 'Update added successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
