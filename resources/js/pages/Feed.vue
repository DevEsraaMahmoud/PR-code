<template>
  <AppLayout>
    <div class="feed-page">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">PR Code Feed</h1>
        <Link
          v-if="$page.props.auth?.user"
          href="/posts/create"
          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-sm hover:shadow-md font-medium"
        >
          + New Post
        </Link>
      </div>

      <!-- Posts List -->
      <div v-if="posts.length === 0" class="text-center py-12 text-gray-500">
        <p class="text-lg mb-2">No posts yet</p>
        <p class="text-sm">Be the first to share your code!</p>
      </div>

      <div v-else class="space-y-6">
        <PostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
        />
      </div>

      <!-- Pagination (if needed) -->
      <div v-if="links && links.length > 0" class="mt-8 flex justify-center">
        <!-- Add pagination component here if needed -->
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PostCard from '@/Components/PostCard.vue';

/**
 * Props expected from Laravel controller:
 * 
 * @prop {Array} posts - Array of post objects
 * @prop {Object} links - Pagination links (optional)
 * 
 * Post shape:
 * {
 *   id: number,
 *   author: { id, name, avatar_url, handle },
 *   title: string,
 *   body: string (HTML),
 *   code: { language: string, content: string },
 *   stats: { likes: number, comments: number, views: number },
 *   created_at: string (ISO date)
 * }
 */
defineProps<{
  posts?: any[];
  links?: any;
}>();
</script>

<style scoped>
.feed-page {
  min-height: calc(100vh - 200px);
}
</style>

