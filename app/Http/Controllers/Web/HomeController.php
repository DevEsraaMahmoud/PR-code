<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {
    }

    public function index(Request $request)
    {
        $query = $request->query('q');
        $language = $request->query('language');

        if ($query || $language) {
            $result = $this->postService->searchPosts($query ?? '', $language);
            
            // Handle paginator or array response
            if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $posts = $result->items();
            } elseif (is_array($result) && isset($result['posts'])) {
                $posts = $result['posts'];
            } else {
                $posts = [];
            }
        } else {
            $result = $this->postService->getAllPosts();
            
            // Handle paginator response - getAllPosts returns LengthAwarePaginator
            if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $posts = $result->items();
            } elseif (is_array($result) && isset($result['posts'])) {
                $posts = $result['posts'];
            } else {
                $posts = [];
            }
        }

        return Inertia::render('Home', [
            'posts' => $posts,
        ]);
    }
}
