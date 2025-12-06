<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('post.{postId}', function ($user, $postId) {
    // Allow authenticated users to listen to post comments
    return $user !== null;
});

