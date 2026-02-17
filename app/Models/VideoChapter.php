<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoChapter extends Model
{
    protected $fillable = [
        'video_id',
        'title',
        'start_time',
        'end_time',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'decimal:2',
            'end_time' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
