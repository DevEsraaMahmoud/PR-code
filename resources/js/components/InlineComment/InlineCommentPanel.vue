<template>
  <Teleport to="body">
    <div
      v-if="visible && lineNumber"
      ref="panelRef"
      class="inline-comment-panel fixed z-[9999] bg-white dark:bg-gray-800 rounded-lg shadow-xl border-2 border-blue-500 w-80 max-w-[calc(100vw-2rem)]"
      :style="panelStyle"
      role="dialog"
      aria-labelledby="panel-title"
      aria-modal="true"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 id="panel-title" class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          Line {{ lineNumber }}
          <span v-if="comments.length > 0" class="text-xs text-gray-500 dark:text-gray-400 ml-2">
            ({{ comments.length }})
          </span>
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          aria-label="Close comment panel"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Comments List -->
      <div class="max-h-64 overflow-y-auto">
        <div
          v-if="comments.length > 0"
          class="divide-y divide-gray-200 dark:divide-gray-700"
        >
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="px-4 py-3"
          >
            <div class="flex items-start gap-2">
              <div
                class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
              >
                {{ comment.user?.name?.charAt(0).toUpperCase() || 'U' }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                    {{ comment.user?.name || 'Anonymous' }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(comment.created_at || comment.created_at) }}
                  </span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                  {{ comment.text || comment.body || comment.content }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div
          v-else
          class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400"
        >
          No comments yet
        </div>
      </div>

      <!-- Comment Form -->
      <div
        v-if="isAuthenticated"
        class="p-4 border-t border-gray-200 dark:border-gray-700"
      >
        <textarea
          v-model="commentText"
          ref="textareaRef"
          placeholder="Add a comment on this line..."
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          rows="3"
          @keydown.ctrl.enter="submitComment"
          @keydown.meta.enter="submitComment"
        ></textarea>
        <div class="flex items-center justify-between mt-2">
          <span class="text-xs text-gray-500 dark:text-gray-400">Press Ctrl+Enter to submit</span>
          <button
            @click="submitComment"
            :disabled="!commentText.trim() || submitting"
            class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
          >
            {{ submitting ? 'Posting...' : 'Comment' }}
          </button>
        </div>
      </div>
      <div
        v-else
        class="p-4 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-500 dark:text-gray-400"
      >
        <a href="/login" class="text-blue-600 dark:text-blue-400 hover:underline">
          Sign in
        </a>
        to add a comment
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  visible?: boolean;
  lineNumber?: number | null;
  comments?: any[];
  position?: { top: number; left: number };
  isAuthenticated?: boolean;
  blockId?: number | null;
  postId?: number | string;
}>();

const emit = defineEmits<{
  close: [];
  'submit-comment': [{ lineNumber: number; text: string; blockId: number | null }];
}>();

const panelRef = ref<HTMLElement | null>(null);
const textareaRef = ref<HTMLTextAreaElement | null>(null);
const commentText = ref('');
const submitting = ref(false);

const panelStyle = computed(() => {
  if (!props.position) {
    return {
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
    };
  }
  
  const top = typeof props.position.top === 'number' ? props.position.top : 0;
  const left = typeof props.position.left === 'number' ? props.position.left : 0;
  
  // Clamp to viewport bounds
  const viewportWidth = typeof window !== 'undefined' ? window.innerWidth : 1920;
  const viewportHeight = typeof window !== 'undefined' ? window.innerHeight : 1080;
  const panelWidth = 320; // w-80 = 320px
  const panelHeight = 400; // approximate height
  
  const clampedLeft = Math.max(10, Math.min(left, viewportWidth - panelWidth - 10));
  const clampedTop = Math.max(10, Math.min(top, viewportHeight - panelHeight - 10));
  
  return {
    top: `${clampedTop}px`,
    left: `${clampedLeft}px`,
    position: 'fixed' as const,
    zIndex: '9999',
  };
});

function formatDate(dateString?: string): string {
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

async function submitComment() {
  if (!commentText.value.trim() || submitting.value || !props.lineNumber) return;

  submitting.value = true;
  emit('submit-comment', {
    lineNumber: props.lineNumber,
    text: commentText.value.trim(),
    blockId: props.blockId || null,
  });
  
  await nextTick();
  commentText.value = '';
  submitting.value = false;
}

function handleClickOutside(event: MouseEvent) {
  if (panelRef.value && !panelRef.value.contains(event.target as Node)) {
    emit('close');
  }
}

function handleEscape(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    emit('close');
  }
}

watch(() => props.visible, async (newVal) => {
  if (newVal) {
    await nextTick();
    if (textareaRef.value) {
      textareaRef.value.focus();
    }
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleEscape);
  } else {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleEscape);
    commentText.value = '';
  }
});

watch(() => props.lineNumber, () => {
  commentText.value = '';
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscape);
});
</script>

<style scoped>
.inline-comment-panel {
  max-height: 80vh;
  overflow-y: auto;
}
</style>


