<template>
  <Teleport to="body">
    <div
      v-if="visible && lineNumber"
      ref="popoverRef"
      class="inline-comment-popover fixed z-[9999] bg-gray-800 rounded-xl shadow-2xl border border-blue-500/50 w-80 max-w-[calc(100vw-2rem)] backdrop-blur-sm"
      :style="popoverStyle"
      role="dialog"
      aria-labelledby="popover-title"
      aria-modal="true"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-700 bg-gradient-to-r from-gray-700 to-gray-800 rounded-t-xl flex items-center justify-between">
          <h3 id="popover-title" class="text-sm font-semibold text-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10m-7 4h7" />
            </svg>
            Line {{ lineNumber || '?' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-300 transition-colors p-1 rounded hover:bg-gray-700"
            aria-label="Close comment popover"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Existing Comments -->
        <div v-if="comments && comments.length > 0" class="max-h-64 overflow-y-auto border-b border-gray-700">
          <!-- View Thread Button -->
          <div class="px-4 py-2 bg-blue-900/20 border-b border-blue-800">
            <button
              @click="$emit('view-thread')"
              class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-blue-300 bg-gray-800 border border-blue-700 rounded-lg hover:bg-blue-900/30 transition-colors shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              View Full Thread ({{ comments.length }} {{ comments.length === 1 ? 'comment' : 'comments' }})
            </button>
          </div>
          
          <!-- Comments Preview -->
          <div class="px-2 py-2">
            <p class="text-xs text-gray-500 dark:text-gray-400 px-2 mb-2">Recent comments:</p>
            <div
              v-for="comment in comments.slice(0, 2)"
              :key="comment.id"
              class="px-2 py-2 border-b border-gray-700 last:border-b-0 hover:bg-gray-700/50 transition-colors rounded"
            >
              <div class="flex items-start gap-2">
                <img
                  :src="getAvatarUrl(comment.user?.avatar_url, comment.user?.name, 20)"
                  :alt="comment.user?.name || 'User'"
                  class="w-5 h-5 rounded-full flex-shrink-0"
                  @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(comment.user?.name); }"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium text-gray-100">
                      {{ comment.user?.name || 'Anonymous' }}
                    </span>
                    <span class="text-xs text-gray-400">
                      {{ formatDate(comment.created_at) }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-300 whitespace-pre-wrap line-clamp-2">
                    {{ comment.text || comment.body }}
                  </p>
                </div>
              </div>
            </div>
            <p v-if="comments.length > 2" class="text-xs text-gray-400 px-2 mt-2 text-center">
              +{{ comments.length - 2 }} more comment{{ comments.length - 2 === 1 ? '' : 's' }}
            </p>
          </div>
        </div>

        <!-- Comment Form -->
        <div class="p-4 border-t border-gray-700 bg-gray-900/50 rounded-b-xl">
          <textarea
            v-model="commentText"
            ref="textareaRef"
            :placeholder="comments && comments.length > 0 ? 'Add a reply...' : 'Add a comment on this line...'"
            class="w-full px-3 py-2 text-sm border border-gray-600 rounded-lg bg-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-all"
            rows="3"
            @keydown.ctrl.enter="submitComment"
            @keydown.meta.enter="submitComment"
          ></textarea>
          <div class="flex items-center justify-between mt-3">
            <span class="text-xs text-gray-400">Press Ctrl+Enter to submit</span>
            <button
              @click="submitComment"
              :disabled="!commentText.trim() || submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed rounded-lg transition-all shadow-sm hover:shadow-md"
            >
              {{ submitting ? 'Posting...' : (comments && comments.length > 0 ? 'Reply' : 'Comment') }}
            </button>
          </div>
        </div>
      </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 24);

const props = defineProps<{
  visible?: boolean;
  lineNumber?: number | null;
  comments?: any[];
  position?: { top: number; left: number };
}>();

const emit = defineEmits<{
  close: [];
  'submit-comment': [{ lineNumber: number; text: string }];
  'view-thread': [];
}>();

const popoverRef = ref<HTMLElement | null>(null);
const textareaRef = ref<HTMLTextAreaElement | null>(null);
const commentText = ref('');
const submitting = ref(false);

onMounted(() => {
  console.log('InlineCommentPopover mounted, visible:', props.visible, 'lineNumber:', props.lineNumber);
  if (props.visible && popoverRef.value) {
    const el = popoverRef.value;
    el.style.display = 'block';
    el.style.visibility = 'visible';
    el.style.opacity = '1';
    console.log('Popover element forced visible on mount');
  }
});

const popoverStyle = computed(() => {
  if (!props.position) {
    console.warn('Popover position not provided, using default');
    return {
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
    };
  }
  
  // Ensure position values are valid numbers
  const top = typeof props.position.top === 'number' ? props.position.top : 0;
  const left = typeof props.position.left === 'number' ? props.position.left : 0;
  
  // Clamp to viewport bounds
  const viewportWidth = typeof window !== 'undefined' ? window.innerWidth : 1920;
  const viewportHeight = typeof window !== 'undefined' ? window.innerHeight : 1080;
  const popoverWidth = 320; // w-80 = 320px
  const popoverHeight = 300; // approximate height
  
  const clampedLeft = Math.max(10, Math.min(left, viewportWidth - popoverWidth - 10));
  const clampedTop = Math.max(10, Math.min(top, viewportHeight - popoverHeight - 10));
  
  const style = {
    top: `${clampedTop}px`,
    left: `${clampedLeft}px`,
    position: 'fixed' as const,
    zIndex: '9999',
  };
  
  console.log('Popover style computed:', style, 'from position:', props.position, 'clamped from:', { top, left });
  return style;
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

async function submitComment() {
  if (!commentText.value.trim() || submitting.value || !props.lineNumber) return;

  submitting.value = true;
  const commentTextValue = commentText.value.trim();
  
  emit('submit-comment', {
    lineNumber: props.lineNumber,
    text: commentTextValue,
  });
  
  // Clear form immediately for better UX
  commentText.value = '';
  
  // Keep submitting state briefly to show feedback
  await new Promise(resolve => setTimeout(resolve, 500));
  submitting.value = false;
}

function handleClickOutside(event) {
  if (popoverRef.value && !popoverRef.value.contains(event.target)) {
    emit('close');
  }
}

function handleEscape(event) {
  if (event.key === 'Escape') {
    emit('close');
  }
}

watch(() => props.visible, async (newVal) => {
  console.log('Popover visible watch triggered:', newVal, 'lineNumber:', props.lineNumber, 'position:', props.position);
  if (newVal) {
    await nextTick();
    console.log('Popover should be visible now, checking ref:', popoverRef.value);
    if (popoverRef.value) {
      const el = popoverRef.value;
      const rect = el.getBoundingClientRect();
      console.log('Popover element position:', rect, 'isVisible:', rect.width > 0 && rect.height > 0);
      // Force visibility
      el.style.display = 'block';
      el.style.visibility = 'visible';
      el.style.opacity = '1';
      el.style.zIndex = '9999';
      console.log('Popover styles applied:', {
        display: el.style.display,
        visibility: el.style.visibility,
        opacity: el.style.opacity,
        zIndex: el.style.zIndex,
        top: el.style.top,
        left: el.style.left,
        computedStyle: window.getComputedStyle(el).display
      });
    }
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

// Also watch for lineNumber changes
watch(() => props.lineNumber, (newVal) => {
  console.log('LineNumber changed:', newVal);
  if (newVal && props.visible && popoverRef.value) {
    const el = popoverRef.value;
    el.style.display = 'block';
    el.style.visibility = 'visible';
    el.style.opacity = '1';
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscape);
});
</script>

