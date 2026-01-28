<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'filename',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'is_folder',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(SharedFile::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SharedFile::class, 'parent_id');
    }
}
