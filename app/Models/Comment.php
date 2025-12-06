<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'snippet_id',
        'parent_id',
        'is_inline',
        'start_line',
        'end_line',
        'body',
        'edited_at',
    ];

    protected $casts = [
        'is_inline' => 'boolean',
        'start_line' => 'integer',
        'end_line' => 'integer',
        'edited_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($comment) {
            if ($comment->isDirty('body')) {
                $comment->edited_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function snippet(): BelongsTo
    {
        return $this->belongsTo(Snippet::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
