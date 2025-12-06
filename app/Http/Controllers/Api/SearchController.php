<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {
    }

    /**
     * Search posts by query, type, tags, or language.
     * 
     * GET /api/search?q=term&type=posts|code&tags=tag1,tag2&language=javascript&page=1
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $type = $request->query('type', 'posts');
        $tags = $request->query('tags') ? explode(',', $request->query('tags')) : null;
        $language = $request->query('language');
        $perPage = (int) ($request->query('per_page', 15));

        $result = $this->searchService->search($query, $type, $tags, $language, $perPage);

        if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return PostResource::collection($result)->response();
        }

        return response()->json([
            'data' => PostResource::collection($result),
        ]);
    }
}
