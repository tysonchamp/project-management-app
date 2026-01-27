<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
