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
        } else {
            $result = $this->postService->getAllPosts();
        }

        return Inertia::render('Home', [
            'posts' => $result['posts'],
        ]);
    }
}
