// Check Config
echo "App Timezone: " . config('app.timezone') . "\n";
echo "DB Timezone: " . config('database.connections.mysql.timezone') . "\n";
echo "System Time: " . date('Y-m-d H:i:s') . "\n";
echo "Now() Time: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Now() Timezone: " . now()->timezoneName . "\n";

// Test Model Saving
\App\Models\TimeEntry::truncate();
$task = \App\Models\Task::first();
$user = \App\Models\User::first();

echo "Creating entry...\n";
$entry = \App\Models\TimeEntry::create([
    'task_id' => $task->id,
    'user_id' => $user->id,
    'start_time' => now(),
]);

echo "Entry created. Start Time (DB Raw): " . \DB::table('time_entries')->first()->start_time . "\n";

// Read back
$entry = $entry->fresh();
echo "Entry read back. Start Time (Model): " . $entry->start_time->format('Y-m-d H:i:s') . "\n";

// Diff
$diff = now()->diffInSeconds($entry->start_time);
echo "Diff (now - start_time): " . $diff . " seconds\n";
