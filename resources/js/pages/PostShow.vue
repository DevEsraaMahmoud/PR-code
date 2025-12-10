<template>
  <AppLayout>
    <div class="post-show-page">
      <div class="max-w-7xl mx-auto">
      <!-- Back Button -->
      <Link
        href="/"
        class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-100 mb-6 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Feed
      </Link>

      <!-- Post Header -->
      <article class="bg-gray-800 rounded-xl shadow-md border border-gray-700 mb-6">
        <div class="px-6 py-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div v-if="post.user || post.author" class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ (post.user?.name || post.author?.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-gray-100">
                    {{ post.user?.name || post.author?.name || 'Anonymous' }}
                  </span>
                  <span v-if="post.user?.handle || post.author?.handle" class="text-sm text-gray-400">
                    @{{ post.user?.handle || post.author?.handle }}
                  </span>
                </div>
                <span class="text-xs text-gray-400">
                  {{ formatDate(post.created_at) }}
                </span>
              </div>
            </div>
            <FollowButton
              v-if="(post.user || post.author) && currentUserId !== (post.user?.id || post.author?.id)"
              :user-id="post.user?.id || post.author?.id"
              :initial-following="post.user?.is_following || post.author?.is_following || false"
            />
          </div>
        </div>

        <div class="px-6 py-6">
          <h1 class="text-3xl font-bold text-gray-100 mb-4">
            {{ post.title }}
          </h1>

          <!-- Rich text body -->
          <div
            class="prose prose-lg max-w-none mb-6 text-gray-300 prose-invert"
            v-html="post.body_html || post.body"
          ></div>

          <!-- Code Block with Inline Comments -->
          <!-- Support both new format (post.code) and existing format (post.snippets) -->
          <div v-if="post.code?.content || (post.snippets && post.snippets.length > 0)" class="mb-6">
            <InlineCommentsLayer
              v-if="post.code?.content"
              :code="post.code"
              :inline-comments="post.inline_comments || []"
              :post-id="post.id"
            />
            <!-- Fallback to existing PostBlock component if using snippets format -->
            <div v-else>
              <p class="text-sm text-gray-400 mb-2">Code blocks available - using existing component structure</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-4 pt-4 border-t border-gray-700">
            <LikeButton
              :post-id="post.id"
              :initial-liked="post.is_liked || false"
              :initial-count="post.stats?.likes || post.likes_count || 0"
            />
            <ShareButton
              :post-id="post.id"
              :post-title="post.title"
              :post-url="postUrl"
            />
            <div class="flex items-center gap-1 text-sm text-gray-400 ml-auto">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span>{{ post.stats?.views || post.views || 0 }} views</span>
            </div>
          </div>
        </div>
      </article>

      <!-- General Comments -->
      <div class="bg-gray-800 rounded-xl shadow-md border border-gray-700 p-6">
        <CommentList
          :comments="post.comments || []"
          :post-id="post.id"
          :current-user-id="currentUserId"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InlineCommentsLayer from '@/components/CodeBlock/InlineCommentsLayer.vue';
import CommentList from '@/components/Comments/CommentList.vue';
import LikeButton from '@/components/Controls/LikeButton.vue';
import ShareButton from '@/components/Controls/ShareButton.vue';
import FollowButton from '@/components/Controls/FollowButton.vue';

const page = usePage();

/**
 * Props expected from Laravel controller:
 * 
 * @prop {Object} post - Single post object
 * @prop {number|string} currentUserId - Current authenticated user ID (optional)
 * 
 * Post shape:
 * {
 *   id: number,
 *   author: { id, name, avatar_url, handle, is_following? },
 *   title: string,
 *   body_html: string (HTML),
 *   code: { language: string, content: string },
 *   inline_comments: [
 *     { id, user: { id, name, avatar_url }, line_number, text, created_at }
 *   ],
 *   comments: [
 *     { id, user: { id, name, avatar_url }, text, created_at, parent_id?, replies?: [] }
 *   ],
 *   stats: { likes: number, comments: number, views: number },
 *   is_liked: boolean,
 *   created_at: string (ISO date)
 * }
 */
const props = defineProps<{
  post: any;
}>();

const currentUserId = computed(() => {
  return (page.props as any).auth?.user?.id || null;
});

const postUrl = computed(() => {
  if (typeof window === 'undefined') return '';
  return `${window.location.origin}/posts/${props.post.id}`;
});

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}
</script>

<style scoped>
.post-show-page {
  min-height: calc(100vh - 200px);
}
</style>

