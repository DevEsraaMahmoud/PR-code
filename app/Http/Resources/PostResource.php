<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Build inline comments index: { blockId: { lineNumber: commentCount } }
        $inlineIndex = [];
        
        if ($this->relationLoaded('snippets') && $this->snippets) {
            foreach ($this->snippets as $snippet) {
                $snippetComments = $snippet->relationLoaded('allComments') 
                    ? $snippet->allComments 
                    : ($snippet->relationLoaded('comments') ? $snippet->comments : collect());
                
                $blockId = (string) $snippet->id;
                $inlineIndex[$blockId] = [];
                
                foreach ($snippetComments as $comment) {
                    if ($comment->is_inline && $comment->start_line) {
                        // Count comments per line (including replies)
                        $lineNum = (string) $comment->start_line;
                        if (!isset($inlineIndex[$blockId][$lineNum])) {
                            $inlineIndex[$blockId][$lineNum] = 0;
                        }
                        // Count this comment
                        $inlineIndex[$blockId][$lineNum]++;
                        
                        // Also count replies to this comment
                        if ($comment->relationLoaded('replies')) {
                            $inlineIndex[$blockId][$lineNum] += $comment->replies->count();
                        }
                    }
                }
            }
        }
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'visibility' => $this->visibility,
            'user' => $this->when(
                $this->relationLoaded('user') && $this->user,
                [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                null
            ),
            'snippets' => $this->whenLoaded('snippets', function () use ($request) {
                if (!$this->snippets) {
                    return [];
                }
                return collect($this->snippets)->map(function ($snippet) use ($request) {
                    return (new SnippetResource($snippet))->toArray($request);
                })->values()->all();
            }, []),
            'comments' => $this->whenLoaded('comments', function () use ($request) {
                if (!$this->comments) {
                    return [];
                }
                return collect($this->comments)->map(function ($comment) use ($request) {
                    return (new \App\Http\Resources\CommentResource($comment))->toArray($request);
                })->values()->all();
            }, []),
            'inlineIndex' => $inlineIndex, // New format: { blockId: { lineNumber: count } }
            'inline_comments_index' => $inlineIndex, // Keep for backward compatibility
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
