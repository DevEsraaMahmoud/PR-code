<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SnippetResource extends JsonResource
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
            'language' => $this->language,
            'code_text' => $this->code_text,
            'block_index' => $this->block_index,
            'comments' => $this->whenLoaded('comments', function () use ($request) {
                if (!$this->comments) {
                    return [];
                }
                return collect($this->comments)->map(function ($comment) use ($request) {
                    return (new CommentResource($comment))->toArray($request);
                })->values()->all();
            }, []),
            'allComments' => $this->whenLoaded('allComments', function () use ($request) {
                if (!$this->allComments) {
                    return [];
                }
                return collect($this->allComments)->map(function ($comment) use ($request) {
                    return (new CommentResource($comment))->toArray($request);
                })->values()->all();
            }, []),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
