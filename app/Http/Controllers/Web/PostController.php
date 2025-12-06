<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Services\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
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

        return Inertia::render('Posts/Index', [
            'posts' => $result['posts'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Posts/Create');
    }

    public function store(StorePostRequest $request)
    {
        try {
            $result = $this->postService->createPost($request->validated(), $request->user()->id);

            return redirect()->route('posts.show', $result['post']->id)
                ->with('message', 'Post created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create post: ' . $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $result = $this->postService->getPost($id);

        if (!$result) {
            abort(404);
        }

        return Inertia::render('Posts/Show', [
            'post' => $result['post'],
        ]);
    }

    public function edit(int $id)
    {
        $result = $this->postService->getPost($id);

        if (!$result || $result['post']->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Posts/Edit', [
            'post' => $result['post'],
        ]);
    }

    public function update(StorePostRequest $request, int $id)
    {
        try {
            $result = $this->postService->updatePost($id, $request->validated(), $request->user()->id);

            return redirect()->route('posts.show', $id)
                ->with('message', 'Post updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update post: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $this->postService->deletePost($id, $request->user()->id);

            return redirect()->route('home')
                ->with('message', 'Post deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete post: ' . $e->getMessage()]);
        }
    }
}
