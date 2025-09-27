<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'app_versions';

    protected $fillable = [
        'version_number',
        'version_name',
        'release_notes',
        'download_url',
        'force_update',
        'min_supported_version',
        'is_active'
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}