<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'duration',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(VideoChapter::class)->orderBy('order');
    }
}
