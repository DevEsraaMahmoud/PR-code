<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Listeners\SendCommentNotification;
use App\Policies\PostPolicy;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\SnippetRepository;
use App\Services\CommentService;
use App\Services\PostService;
use App\Services\SearchService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Post::class => PostPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(PostRepository::class);
        $this->app->singleton(SnippetRepository::class);
        $this->app->singleton(CommentRepository::class);

        // Register services
        $this->app->singleton(PostService::class, function ($app) {
            return new PostService(
                $app->make(PostRepository::class),
                $app->make(SnippetRepository::class)
            );
        });

            $this->app->singleton(CommentService::class, function ($app) {
                return new CommentService(
                    $app->make(CommentRepository::class),
                    $app->make(PostRepository::class),
                    $app->make(SnippetRepository::class)
                );
            });
            $this->app->singleton(SearchService::class, function ($app) {
                return new SearchService(
                    $app->make(PostRepository::class)
                );
            });
        }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            CommentCreated::class,
            SendCommentNotification::class,
        );
    }
}
