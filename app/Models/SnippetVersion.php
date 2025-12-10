<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnippetVersion extends Model
{
    protected $fillable = [
        'snippet_id',
        'content',
        'version_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }
}


