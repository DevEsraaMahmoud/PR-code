<template>
  <div class="w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-100">
        Comments
        <span v-if="comments && comments.length > 0" class="text-sm font-normal text-gray-400">
          ({{ comments.length }})
        </span>
      </h3>
    </div>

    <!-- Comment Form -->
    <div class="mb-6 p-4 bg-gray-700/50 rounded-lg border border-gray-600">
      <textarea
        v-model="newCommentText"
        ref="commentTextareaRef"
        placeholder="Write a comment..."
        class="w-full px-3 py-2 text-sm border border-gray-600 rounded-md bg-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
        rows="3"
        @keydown.ctrl.enter="submitComment"
        @keydown.meta.enter="submitComment"
      ></textarea>
      <div class="flex items-center justify-between mt-2">
        <span class="text-xs text-gray-400">Press Ctrl+Enter to submit</span>
        <button
          @click="submitComment"
          :disabled="!newCommentText.trim() || submitting"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
        >
          {{ submitting ? 'Posting...' : 'Post Comment' }}
        </button>
      </div>
    </div>

    <!-- Comments -->
    <div v-if="(!comments || comments.length === 0) && !submitting" class="text-center py-8 text-gray-400">
      No comments yet. Be the first to comment!
    </div>

    <div v-else-if="topLevelComments.length > 0" class="space-y-4">
      <CommentItem
        v-for="comment in topLevelComments"
        :key="comment.id"
        :comment="comment"
        :post-id="postId"
        :current-user-id="currentUserId"
        @reply-added="handleReplyAdded"
        @comment-deleted="handleCommentDeleted"
      />
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-4 text-gray-400">
      Loading comments...
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import CommentItem from './CommentItem.vue';
// @ts-ignore - JS file, no type declarations
import { usePostStore } from '@/Composables/usePostState';

const props = defineProps<{
  comments?: any[];
  postId: number | string;
  currentUserId?: number | string | null;
  loading?: boolean;
}>();

const emit = defineEmits<{
  'comment-added': [{ text: string }];
  'comment-deleted': [commentId: number | string];
}>();

const newCommentText = ref('');
const submitting = ref(false);
const commentTextareaRef = ref<HTMLTextAreaElement | null>(null);
const postStore = usePostStore();

const topLevelComments = computed(() => {
  // Filter to only top-level comments (no parent_id)
  if (!props.comments || !Array.isArray(props.comments)) return [];
  return props.comments.filter((comment) => !comment.parent_id);
});

async function submitComment() {
  if (!newCommentText.value.trim() || submitting.value) return;

  submitting.value = true;

  // Optimistic update
  const optimisticComment = {
    id: `pending-${Date.now()}`,
    text: newCommentText.value.trim(),
    user: props.currentUserId ? { id: props.currentUserId } : null,
    created_at: new Date().toISOString(),
    isPending: true,
    replies: [],
  };

  postStore.addOptimisticComment(props.postId, optimisticComment);

  router.post(
    `/posts/${props.postId}/comments`,
    {
      text: newCommentText.value.trim(),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        emit('comment-added', {
          text: newCommentText.value.trim(),
        });
        newCommentText.value = '';
        submitting.value = false;
        postStore.clearOptimisticComments(props.postId);
      },
      onError: (errors) => {
        console.error('Failed to add comment:', errors);
        submitting.value = false;
        postStore.removeOptimisticComment(props.postId, optimisticComment.id);
      },
    }
  );
}

function handleReplyAdded(data) {
  emit('comment-added', data);
}

function handleCommentDeleted(commentId) {
  emit('comment-deleted', commentId);
}
</script>

