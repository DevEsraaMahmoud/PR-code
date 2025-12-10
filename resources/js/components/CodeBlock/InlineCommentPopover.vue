<template>
  <Teleport to="body">
    <div
      v-if="visible && lineNumber"
      ref="popoverRef"
      class="inline-comment-popover fixed z-[9999] bg-[#252526] rounded-lg shadow-2xl border border-[#3e3e42] w-80 max-w-[calc(100vw-2rem)]"
      :style="popoverStyle"
      role="dialog"
      aria-labelledby="popover-title"
      aria-modal="true"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-[#3e3e42] bg-[#252526] rounded-t-lg flex items-center justify-between">
          <h3 id="popover-title" class="text-sm font-semibold text-[#cccccc] flex items-center gap-2">
            <svg class="w-4 h-4 text-[#858585]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Line {{ lineNumber || '?' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-[#858585] hover:text-[#cccccc] transition-colors p-1 rounded hover:bg-[#3e3e42]"
            aria-label="Close comment popover"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Existing Comments -->
        <div v-if="comments && comments.length > 0" class="border-b border-[#3e3e42]">
          <!-- View Thread Button -->
          <div class="px-4 py-3 bg-[#1e1e1e] border-b border-[#3e3e42]">
            <button
              @click="showFullThread = !showFullThread"
              class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-medium text-white bg-[#007acc] border border-[#005a9e] rounded hover:bg-[#005a9e] transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              {{ showFullThread ? 'Hide Full Thread' : `View Full Thread (${comments.length} ${comments.length === 1 ? 'comment' : 'comments'})` }}
            </button>
          </div>
          
          <!-- Comments Preview (Collapsed) -->
          <div v-if="!showFullThread" class="px-4 py-3 bg-[#252526] max-h-64 overflow-y-auto">
            <p class="text-xs text-[#858585] mb-3">Recent comments:</p>
            <div class="space-y-3">
              <div
                v-for="comment in comments.slice(0, 2)"
                :key="comment.id"
                class="flex items-start gap-2"
              >
                <div class="w-6 h-6 rounded-full bg-[#007acc] flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                  {{ comment.user?.name?.charAt(0).toUpperCase() || '?' }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium text-[#cccccc]">
                      {{ comment.user?.name || 'Anonymous' }}
                    </span>
                    <span class="text-[10px] text-[#858585]">
                      {{ formatDate(comment.created_at) }}
                    </span>
                  </div>
                  <p class="text-xs text-[#cccccc] whitespace-pre-wrap">
                    {{ comment.text || comment.body }}
                  </p>
                </div>
              </div>
            </div>
            <p v-if="comments.length > 2" class="text-xs text-[#858585] mt-3 text-center">
              +{{ comments.length - 2 }} more comment{{ comments.length - 2 === 1 ? '' : 's' }}
            </p>
          </div>

          <!-- Full Thread View (Expanded) -->
          <div v-else class="px-4 py-3 bg-[#252526] max-h-96 overflow-y-auto">
            <p class="text-xs text-[#858585] mb-3">All comments:</p>
            <div class="space-y-3">
              <div
                v-for="comment in comments"
                :key="comment.id"
                class="flex items-start gap-2"
              >
                <div class="w-6 h-6 rounded-full bg-[#007acc] flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                  {{ comment.user?.name?.charAt(0).toUpperCase() || '?' }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-medium text-[#cccccc]">
                      {{ comment.user?.name || 'Anonymous' }}
                    </span>
                    <span class="text-[10px] text-[#858585]">
                      {{ formatDate(comment.created_at) }}
                    </span>
                  </div>
                  <p class="text-xs text-[#cccccc] whitespace-pre-wrap mb-2">
                    {{ comment.text || comment.body }}
                  </p>
                  <!-- Emoji Reactions -->
                  <div class="flex items-center gap-1 mt-2">
                    <button
                      @click.stop="toggleReaction(comment.id, '👍')"
                      class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                      :class="hasReaction(comment.id, '👍') ? 'bg-[#3e3e42]' : ''"
                    >
                      <span>👍</span>
                      <span class="text-[#858585]">{{ getReactionCount(comment.id, '👍') }}</span>
                    </button>
                    <button
                      @click.stop="toggleReaction(comment.id, '😄')"
                      class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                      :class="hasReaction(comment.id, '😄') ? 'bg-[#3e3e42]' : ''"
                    >
                      <span>😄</span>
                      <span class="text-[#858585]">{{ getReactionCount(comment.id, '😄') }}</span>
                    </button>
                    <button
                      @click.stop="toggleReaction(comment.id, '👀')"
                      class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                      :class="hasReaction(comment.id, '👀') ? 'bg-[#3e3e42]' : ''"
                    >
                      <span>👀</span>
                      <span class="text-[#858585]">{{ getReactionCount(comment.id, '👀') }}</span>
                    </button>
                    <button
                      @click.stop="showReactionPicker(comment.id)"
                      class="px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[#858585] hover:text-[#cccccc] text-[10px]"
                      title="Add reaction"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Comment Form -->
        <div class="p-4 border-t border-[#3e3e42] bg-[#252526] rounded-b-lg">
          <textarea
            v-model="commentText"
            ref="textareaRef"
            :placeholder="comments && comments.length > 0 ? 'Add a reply...' : 'Add a comment on this line...'"
            class="w-full px-3 py-2 text-sm border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] resize-none transition-all"
            rows="3"
            @keydown.ctrl.enter="submitComment"
            @keydown.meta.enter="submitComment"
          ></textarea>
          <div class="flex items-center justify-between mt-3">
            <span class="text-xs text-[#858585]">Press Ctrl+Enter to submit</span>
            <button
              @click="submitComment"
              :disabled="!commentText.trim() || submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-[#3e3e42] hover:bg-[#4e4e4e] disabled:bg-[#2a2d2e] disabled:text-[#858585] disabled:cursor-not-allowed rounded transition-colors"
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
}>();

const popoverRef = ref<HTMLElement | null>(null);
const textareaRef = ref<HTMLTextAreaElement | null>(null);
const commentText = ref('');
const submitting = ref(false);
const showFullThread = ref(false);
const reactions = ref<Record<string, Record<string, number>>>({}); // { commentId: { emoji: count } }
const userReactions = ref<Record<string, string[]>>({}); // { commentId: [emoji1, emoji2] }
const showReactionPickerFor = ref<string | null>(null);

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

function hasReaction(commentId: number | string, emoji: string): boolean {
  return userReactions.value[String(commentId)]?.includes(emoji) || false;
}

function getReactionCount(commentId: number | string, emoji: string): number {
  return reactions.value[String(commentId)]?.[emoji] || 0;
}

async function toggleReaction(commentId: number | string, emoji: string) {
  const cId = String(commentId);
  const hasReacted = hasReaction(commentId, emoji);
  
  try {
    const response = await fetch(`/api/comments/${commentId}/reactions`, {
      method: hasReacted ? 'DELETE' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ emoji }),
    });
    
    if (response.ok) {
      if (hasReacted) {
        // Remove reaction
        if (reactions.value[cId]?.[emoji]) {
          reactions.value[cId][emoji]--;
          if (reactions.value[cId][emoji] === 0) {
            delete reactions.value[cId][emoji];
          }
        }
        userReactions.value[cId] = userReactions.value[cId]?.filter((e: string) => e !== emoji) || [];
      } else {
        // Add reaction
        if (!reactions.value[cId]) {
          reactions.value[cId] = {};
        }
        reactions.value[cId][emoji] = (reactions.value[cId][emoji] || 0) + 1;
        if (!userReactions.value[cId]) {
          userReactions.value[cId] = [];
        }
        userReactions.value[cId].push(emoji);
      }
    }
  } catch (error) {
    console.error('Failed to toggle reaction:', error);
  }
}

function showReactionPicker(commentId: number | string) {
  showReactionPickerFor.value = showReactionPickerFor.value === String(commentId) ? null : String(commentId);
}
</script>

