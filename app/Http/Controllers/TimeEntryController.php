<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    public function start(Task $task)
    {
        // Stop any currently running timer for this user
        $activeEntry = TimeEntry::where('user_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        if ($activeEntry) {
            $activeEntry->update([
                'end_time' => now(),
                'duration' => abs(now()->diffInSeconds($activeEntry->start_time))
            ]);
        }

        // Start new timer
        $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'start_time' => now(),
        ]);

        \App\Services\LogActivity::record('start_timer', "Started timer on task: {$task->title}", $task);

        return back()->with('success', 'Timer started.');
    }

    public function stop(Task $task)
    {
        $activeEntry = TimeEntry::where('user_id', Auth::id())
            ->where('task_id', $task->id)
            ->whereNull('end_time')
            ->first();

        if ($activeEntry) {
            $activeEntry->update([
                'end_time' => now(),
                'duration' => abs(now()->diffInSeconds($activeEntry->start_time))
            ]);
            
            \App\Services\LogActivity::record('stop_timer', "Stopped timer on task: {$task->title}", $task);
            return back()->with('success', 'Timer stopped.');
        }

        return back()->with('error', 'No active timer found for this task.');
    }
}
