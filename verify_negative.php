// 1. Check diffInSeconds absolute
$now = now();
$future = now()->addHour();
echo "Diff (Future): " . $now->diffInSeconds($future) . "\n"; // Should be positive
echo "Diff (Future, Not Absolute): " . $now->diffInSeconds($future, false) . "\n"; // Should be negative

// 2. Check gmdate with negative
echo "gmdate(-3600): " . gmdate("H:i:s", -3600) . "\n";
echo "gmdate(-60): " . gmdate("H:i:s", -60) . "\n";

// 3. Simulate User Scenario
// User says "23 hours"
// If gmdate(-3600) is "23:00:00", then we have a negative duration of 1 hour.
// How do we get -1 hour duration?
// If we used diffInSeconds($start, false)?
// In Controller: `now()->diffInSeconds($activeEntry->start_time)`
// It uses default absolute=true.
// HOWEVER, $activeEntry->start_time is the argument.
// `now()->diffInSeconds($start)`.
// If $start is in future, diff is positive.
// If $start is in past, diff is positive.
// So duration calculated in Controller should ALWAYS be positive.

// 4. Check Display Logic in Blade
// $totalSeconds = $task->timeEntries->sum('duration') + ($activeTimer ? now()->diffInSeconds($activeTimer->start_time) : 0);
// This also uses default absolute.

// 5. What if `timeEntries->sum('duration')` is HUGE?
// Did I fail to clear old entries? 
// User said "I created a fake task".
// If he created a fake task, it has NO entries.
// He started timer. 1 minute later.
// He sees 23 hours.
// Maybe `now()` vs `start_time` timezone diff is causing it?
// If `start_time` is interpreted as being "Yesterday"?
// If DB saved "2026-01-11 17:21:45" (UTC)
// And Read back, it thinks it's "2026-01-10 ..." ? Why?

// 6. Check if `timezone` config in database.php
