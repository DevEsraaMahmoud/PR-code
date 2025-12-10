<template>
  <div
    ref="panelRef"
    class="inline-comment-panel bg-[#252526] border-l border-[#3e3e42] flex flex-col h-full transition-all duration-300"
    :class="{
      'w-0 overflow-hidden': !isOpen,
      'w-96': isOpen,
    }"
    role="complementary"
    aria-label="Inline comments panel"
  >
    <!-- Header -->
    <div
      v-if="isOpen"
      class="flex items-center justify-between px-4 py-3 border-b border-[#3e3e42] flex-shrink-0"
    >
      <h3 class="text-sm font-semibold text-[#cccccc]">
        Inline Comments
        <span v-if="threads.length > 0" class="text-xs text-[#858585] ml-2">
          ({{ threads.length }})
        </span>
      </h3>
      <button
        @click="$emit('toggle')"
        class="text-[#858585] hover:text-[#cccccc] transition-colors p-1 rounded hover:bg-[#3e3e42]"
        aria-label="Close comment panel"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Toggle Button (when closed) -->
    <button
      v-if="!isOpen"
      @click="$emit('toggle')"
      class="absolute right-0 top-1/2 -translate-y-1/2 bg-[#252526] border border-[#3e3e42] border-l-0 rounded-r-lg px-2 py-8 text-[#858585] hover:text-[#cccccc] transition-colors"
      aria-label="Open comment panel"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>

    <!-- Threads List -->
    <div
      v-if="isOpen"
      class="flex-1 overflow-y-auto"
    >
      <div v-if="threads.length === 0" class="px-4 py-8 text-center">
        <p class="text-sm text-[#858585] mb-2">No inline comments yet</p>
        <p class="text-xs text-[#858585]">Click on a code line to add a comment</p>
      </div>

      <div v-else class="divide-y divide-[#3e3e42]">
        <div
          v-for="thread in sortedThreads"
          :key="`thread-${thread.blockId}-${thread.lineNumber}`"
          class="px-4 py-3 hover:bg-[#2a2d2e] transition-colors cursor-pointer"
          :class="{
            'bg-[#094771]': activeThread?.lineNumber === thread.lineNumber && activeThread?.blockId === thread.blockId,
            'opacity-60': thread.resolved,
          }"
          @click="handleThreadClick(thread)"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-[#007acc]">
                  Line {{ thread.lineNumber }}
                </span>
                <span
                  v-if="thread.resolved"
                  class="text-xs px-1.5 py-0.5 bg-green-900/30 text-green-400 rounded"
                  title="Resolved"
                >
                  ✓ Resolved
                </span>
                <span
                  v-else
                  class="text-xs px-1.5 py-0.5 bg-yellow-900/30 text-yellow-400 rounded"
                  title="Unresolved"
                >
                  Open
                </span>
              </div>
              <div class="text-xs text-[#858585] mb-1">
                {{ thread.commentCount || thread.messages?.length || 0 }} comment{{ (thread.commentCount || thread.messages?.length || 0) !== 1 ? 's' : '' }}
              </div>
              <div v-if="thread.excerpt" class="text-xs text-[#cccccc] line-clamp-2">
                {{ thread.excerpt }}
              </div>
              <div v-if="thread.author" class="text-xs text-[#858585] mt-1">
                by {{ thread.author }}
              </div>
            </div>
            <div class="flex items-center gap-1 text-xs text-[#858585]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <span>{{ thread.reactionsCount || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Thread View -->
    <div
      v-if="isOpen && activeThread"
      class="border-t border-[#3e3e42] flex-shrink-0"
    >
      <CommentThread
        :thread="activeThread"
        :current-user-id="currentUserId"
        :can-resolve="canResolve"
        @reply="handleReply"
        @resolve="handleResolve"
        @unresolve="handleUnresolve"
        @edit="handleEdit"
        @delete="handleDelete"
        @react="handleReact"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import CommentThread from './CommentThread.vue';

const props = defineProps<{
  isOpen?: boolean;
  threads?: Array<{
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    commentCount?: number;
    messages?: any[];
    resolved?: boolean;
    excerpt?: string;
    author?: string;
    reactionsCount?: number;
  }>;
  activeThread?: {
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    messages: any[];
    resolved: boolean;
  } | null;
  currentUserId?: number;
  canResolve?: boolean;
}>();

const emit = defineEmits<{
  'toggle': [];
  'thread-selected': [thread: { lineNumber: number; blockId: number | string; postId: number | string }];
  'thread-updated': [];
  'thread-resolved': [{ lineNumber: number; blockId: number | string; resolved: boolean }];
  'thread-unresolved': [{ lineNumber: number; blockId: number | string }];
}>();

const panelRef = ref<HTMLElement>();

const sortedThreads = computed(() => {
  if (!props.threads) return [];
  return [...props.threads].sort((a, b) => {
    // Unresolved first
    if (a.resolved !== b.resolved) {
      return a.resolved ? 1 : -1;
    }
    // Then by line number
    return a.lineNumber - b.lineNumber;
  });
});

function handleThreadClick(thread: { lineNumber: number; blockId: number | string; postId: number | string }) {
  emit('thread-selected', thread);
}

function handleReply(commentId: number, body: string) {
  emit('thread-updated');
  // Parent component should handle the API call
}

function handleResolve() {
  if (props.activeThread) {
    emit('thread-resolved', {
      lineNumber: props.activeThread.lineNumber,
      blockId: props.activeThread.blockId,
      resolved: true,
    });
  }
}

function handleUnresolve() {
  if (props.activeThread) {
    emit('thread-unresolved', {
      lineNumber: props.activeThread.lineNumber,
      blockId: props.activeThread.blockId,
    });
  }
}

function handleEdit(comment: any) {
  emit('thread-updated');
}

function handleDelete(commentId: number) {
  emit('thread-updated');
}

function handleReact(commentId: number, reactionType: string) {
  emit('thread-updated');
}
</script>

<style scoped>
.inline-comment-panel {
  position: relative;
}
</style>


