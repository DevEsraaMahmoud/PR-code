<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    protected $casts = [
        'body' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function snippets(): HasMany
    {
        return $this->hasMany(Snippet::class)->orderBy('block_index');
    }

    public function comments(): HasMany
    {
        return $this->hasManyThrough(Comment::class, Snippet::class);
    }
}
