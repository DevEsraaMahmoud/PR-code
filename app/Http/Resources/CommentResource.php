<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'snippet_id' => $this->snippet_id,
            'parent_id' => $this->parent_id,
            'is_inline' => $this->is_inline,
            'start_line' => $this->start_line,
            'end_line' => $this->end_line,
            'body' => $this->body,
            'edited_at' => $this->edited_at?->toISOString(),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'likes_count' => $this->whenLoaded('likes', fn() => $this->likes->count(), 0),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
