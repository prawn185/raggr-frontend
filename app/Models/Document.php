<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'thumbnailPath',
        'type',
        'owner_id',
        'description',
        'status',
        'tags',
        'category',
        'version',
        'is_global',
        'vectorID',
        'content',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getPathAttribute($value)
    {
        return asset('storage/' . $value);
    }

    public function getUrlAttribute()
    {
        return url($this->path);
    }

    public function getSizeAttribute()
    {
        return Storage::size($this->path);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y H:i:s');
    }
}

