<template>
  <div class="py-3">
    <div class="flex gap-3">
      <!-- Avatar -->
      <img
        :src="getAvatarUrl(comment.user?.avatar_url, comment.user?.name, 32)"
        :alt="comment.user?.name || 'User'"
        class="w-8 h-8 rounded-full flex-shrink-0"
        @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(comment.user?.name); }"
      />

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-sm font-medium text-gray-900">
            {{ comment.user?.name || 'Anonymous' }}
          </span>
          <span class="text-xs text-gray-500">
            {{ formatDate(comment.created_at) }}
          </span>
          <span v-if="comment.isPending" class="text-xs text-blue-600">
            Posting...
          </span>
        </div>

        <p class="text-sm text-gray-700 whitespace-pre-wrap mb-2">
          {{ comment.text }}
        </p>

        <!-- Actions -->
        <div class="flex items-center gap-4">
          <button
            @click="toggleReply"
            class="text-xs text-gray-500 hover:text-gray-700 transition-colors"
          >
            {{ showReplyForm ? 'Cancel' : 'Reply' }}
          </button>
          <button
            v-if="canDelete"
            @click="handleDelete"
            class="text-xs text-red-600 hover:text-red-700 transition-colors"
          >
            Delete
          </button>
        </div>

        <!-- Reply Form -->
        <div v-if="showReplyForm" class="mt-3">
          <textarea
            v-model="replyText"
            ref="replyTextareaRef"
            placeholder="Write a reply..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            rows="2"
            @keydown.ctrl.enter="submitReply"
            @keydown.meta.enter="submitReply"
          ></textarea>
          <div class="flex items-center justify-end gap-2 mt-2">
            <button
              @click="toggleReply"
              class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
            >
              Cancel
            </button>
            <button
              @click="submitReply"
              :disabled="!replyText.trim() || submitting"
              class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
            >
              {{ submitting ? 'Posting...' : 'Reply' }}
            </button>
          </div>
        </div>

        <!-- Nested Replies -->
        <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 ml-4 space-y-3 border-l-2 border-gray-200 pl-4">
          <CommentItem
            v-for="reply in comment.replies"
            :key="reply.id"
            :comment="reply"
            :post-id="postId"
            :current-user-id="currentUserId"
            @reply-added="handleReplyAdded"
            @comment-deleted="handleCommentDeleted"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import CommentItem from './CommentItem.vue';
import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 32);

const props = defineProps<{
  comment: any;
  postId: number | string;
  currentUserId?: number | string | null;
}>();

const emit = defineEmits<{
  'reply-added': [data: any];
  'comment-deleted': [commentId: number | string];
}>();

const showReplyForm = ref(false);
const replyText = ref('');
const submitting = ref(false);
const replyTextareaRef = ref<HTMLTextAreaElement | null>(null);

const canDelete = computed(() => {
  return props.currentUserId && props.comment.user?.id === props.currentUserId;
});

function formatDate(dateString: string | undefined | null): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}

function toggleReply() {
  showReplyForm.value = !showReplyForm.value;
  if (showReplyForm.value) {
    nextTick(() => {
      replyTextareaRef.value?.focus();
    });
  } else {
    replyText.value = '';
  }
}

async function submitReply() {
  if (!replyText.value.trim() || submitting.value) return;

  submitting.value = true;
  
  router.post(
    `/posts/${props.postId}/comments`,
    {
      text: replyText.value.trim(),
      parent_id: props.comment.id,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        emit('reply-added', {
          parentId: props.comment.id,
          text: replyText.value.trim(),
        });
        replyText.value = '';
        showReplyForm.value = false;
        submitting.value = false;
      },
      onError: (errors) => {
        console.error('Failed to add reply:', errors);
        submitting.value = false;
      },
    }
  );
}

function handleDelete() {
  if (!confirm('Are you sure you want to delete this comment?')) return;

  router.delete(`/comments/${props.comment.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      emit('comment-deleted', props.comment.id);
    },
    onError: (errors) => {
      console.error('Failed to delete comment:', errors);
    },
  });
}

function handleReplyAdded(data) {
  emit('reply-added', data);
}

function handleCommentDeleted(commentId) {
  emit('comment-deleted', commentId);
}
</script>

