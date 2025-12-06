<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SnippetResource;
use App\Repositories\SnippetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SnippetController extends Controller
{
    public function __construct(
        private SnippetRepository $snippetRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $postId = $request->query('post_id');

        if (!$postId) {
            return response()->json([
                'message' => 'post_id is required',
            ], 422);
        }

        $snippets = $this->snippetRepository->findByPost((int) $postId);

        return response()->json([
            'data' => SnippetResource::collection($snippets),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $snippet = $this->snippetRepository->find((int) $id);

        if (!$snippet) {
            return response()->json([
                'message' => 'Snippet not found',
            ], 404);
        }

        return response()->json([
            'data' => new SnippetResource($snippet),
        ]);
    }
}
