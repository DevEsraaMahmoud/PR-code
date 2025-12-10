<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snippet extends Model
{
    protected $fillable = [
        'post_id',
        'language',
        'code_text',
        'block_index',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('start_line');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('start_line');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SnippetVersion::class)->orderBy('version_number', 'desc');
    }
}
