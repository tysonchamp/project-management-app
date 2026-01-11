<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUpdates extends Model
{
    protected $fillable = ['task_id', 'user_id', 'update'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
