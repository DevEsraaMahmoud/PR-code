<template>
  <div class="bg-[#252526] rounded border border-[#3e3e42] h-fit sticky top-6">
    <!-- Header -->
    <div class="border-b border-[#3e3e42] px-4 py-3">
      <h2 class="text-base font-semibold text-[#cccccc] flex items-center gap-2">
        <svg class="w-4 h-4 text-[#007acc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        Inline Comments
      </h2>
    </div>

    <!-- Content Area -->
    <div class="p-4 max-h-[calc(100vh-200px)] overflow-y-auto">
      <!-- Empty State -->
      <div v-if="allThreads.length === 0" class="text-center py-8 text-[#858585]">
        <svg class="w-10 h-10 mx-auto mb-3 text-[#3e3e42]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <p class="text-sm">Click a line number to start a thread</p>
      </div>

      <!-- Threads List -->
      <div v-else class="space-y-2">
        <button
          v-for="thread in allThreads"
          :key="`${thread.blockId}-${thread.lineNumber}`"
          @click="$emit('thread-selected', thread)"
          class="w-full text-left p-3 rounded border transition-colors"
          :class="isActiveThread(thread)
            ? 'bg-[#094771] border-[#007acc]'
            : 'bg-[#1e1e1e] border-[#3e3e42] hover:bg-[#2a2d2e] hover:border-[#4e4e4e]'"
        >
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-[#007acc] text-white text-xs font-bold">
                {{ thread.lineNumber }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-[#cccccc]">
                  Line {{ thread.lineNumber }}
                </span>
                <span
                  v-if="thread.resolved"
                  class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-medium bg-green-900/40 text-green-300 rounded"
                >
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  Resolved
                </span>
              </div>
              <p class="text-xs text-[#858585] mb-1">
                Block ID: {{ thread.blockId }}
              </p>
              <div class="flex items-center gap-2 text-xs text-[#858585]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>{{ thread.commentCount }} {{ thread.commentCount === 1 ? 'comment' : 'comments' }}</span>
              </div>
            </div>
          </div>
        </button>
      </div>

      <!-- Active Thread Details -->
      <div v-if="activeThread" class="mt-4 pt-4 border-t border-[#3e3e42]">
        <div class="mb-3">
          <h3 class="text-sm font-semibold text-[#cccccc] mb-2 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-[#007acc] text-white text-xs font-bold">
              {{ activeThread.lineNumber }}
            </span>
            Line {{ activeThread.lineNumber }}
          </h3>
        </div>

        <!-- Thread Messages -->
        <div class="space-y-2 mb-4 max-h-64 overflow-y-auto">
          <div
            v-for="message in activeThread.messages"
            :key="message.id"
            class="bg-[#1e1e1e] rounded p-2 border border-[#3e3e42]"
          >
            <div class="flex items-start gap-2">
              <div class="w-6 h-6 rounded-full bg-[#007acc] flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                {{ message.user?.name?.charAt(0).toUpperCase() || '?' }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-medium text-[#cccccc]">
                    {{ message.user?.name || 'Anonymous' }}
                  </span>
                  <span class="text-[10px] text-[#858585]">
                    {{ formatDate(message.created_at) }}
                  </span>
                </div>
                <p class="text-xs text-[#cccccc] whitespace-pre-wrap mb-2">
                  {{ message.text || message.body }}
                </p>
                <!-- Emoji Reactions -->
                <div class="flex items-center gap-1 mt-2">
                  <button
                    @click.stop="toggleReaction(message.id, '👍')"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                    :class="hasReaction(message.id, '👍') ? 'bg-[#3e3e42]' : ''"
                  >
                    <span>👍</span>
                    <span class="text-[#858585]">{{ getReactionCount(message.id, '👍') }}</span>
                  </button>
                  <button
                    @click.stop="toggleReaction(message.id, '😄')"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                    :class="hasReaction(message.id, '😄') ? 'bg-[#3e3e42]' : ''"
                  >
                    <span>😄</span>
                    <span class="text-[#858585]">{{ getReactionCount(message.id, '😄') }}</span>
                  </button>
                  <button
                    @click.stop="toggleReaction(message.id, '👀')"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded hover:bg-[#3e3e42] transition-colors text-[10px]"
                    :class="hasReaction(message.id, '👀') ? 'bg-[#3e3e42]' : ''"
                  >
                    <span>👀</span>
                    <span class="text-[#858585]">{{ getReactionCount(message.id, '👀') }}</span>
                  </button>
                  <button
                    @click.stop="showReactionPicker(message.id)"
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

        <!-- Reply Form -->
        <div class="bg-[#1e1e1e] rounded p-3 border border-[#3e3e42]">
          <textarea
            v-model="replyText"
            ref="replyTextareaRef"
            placeholder="Add a reply..."
            class="w-full px-2 py-1.5 text-xs border border-[#3e3e42] rounded bg-[#252526] text-[#cccccc] placeholder-[#858585] resize-none focus:outline-none focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc]"
            rows="2"
            @keydown.ctrl.enter="submitReply"
            @keydown.meta.enter="submitReply"
          ></textarea>
          <div class="flex items-center justify-between mt-2">
            <span class="text-[10px] text-[#858585]">Ctrl+Enter to send</span>
            <button
              @click="submitReply"
              :disabled="!replyText.trim() || submitting"
              class="px-2 py-1 text-xs font-medium text-white bg-[#007acc] hover:bg-[#005a9e] disabled:bg-[#3e3e42] disabled:cursor-not-allowed rounded transition-colors"
            >
              {{ submitting ? 'Posting...' : 'Reply' }}
            </button>
          </div>
        </div>

        <!-- Thread Actions -->
        <div v-if="canResolve" class="mt-3 pt-3 border-t border-[#3e3e42]">
          <button
            @click="toggleResolve"
            :disabled="submitting"
            class="w-full px-3 py-1.5 text-xs font-medium rounded transition-colors"
            :class="activeThread.resolved
              ? 'bg-yellow-900/30 text-yellow-300 hover:bg-yellow-900/50'
              : 'bg-green-900/30 text-green-300 hover:bg-green-900/50'"
          >
            {{ activeThread.resolved ? 'Unresolve' : 'Resolve' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  inlineCommentsIndex: Record<string, Record<string, number>>;
  activeThread?: {
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    messages: Array<{
      id: number | string;
      user?: { id: number | string; name: string; avatar_url?: string };
      text?: string;
      body?: string;
      created_at: string;
      edited_at?: string;
    }>;
    resolved?: boolean;
  } | null;
  currentUserId?: number | string;
  canResolve?: boolean;
  postId?: number | string;
}>();

const emit = defineEmits<{
  'thread-selected': [{ lineNumber: number; blockId: number | string; postId: number | string }];
  'thread-updated': [];
  'thread-resolved': [{ resolved: boolean }];
}>();

const replyText = ref('');
const submitting = ref(false);
const replyTextareaRef = ref<HTMLTextAreaElement | null>(null);

const canResolve = computed(() => {
  return props.canResolve !== false && !!props.currentUserId;
});

// Build list of all threads from inlineCommentsIndex
const allThreads = computed(() => {
  const threads: Array<{
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    commentCount: number;
    resolved?: boolean;
  }> = [];

  Object.keys(props.inlineCommentsIndex).forEach((blockId) => {
    const lineComments = props.inlineCommentsIndex[blockId];
    Object.keys(lineComments).forEach((lineNumber) => {
      const count = lineComments[lineNumber];
      if (count > 0) {
        threads.push({
          lineNumber: parseInt(lineNumber),
          blockId,
          postId: props.postId || 0,
          commentCount: count,
          resolved: props.activeThread?.blockId === blockId && 
                    props.activeThread?.lineNumber === parseInt(lineNumber) 
                    ? props.activeThread.resolved 
                    : false,
        });
      }
    });
  });

  // Sort by line number
  threads.sort((a, b) => a.lineNumber - b.lineNumber);
  return threads;
});

function isActiveThread(thread: { lineNumber: number; blockId: number | string }) {
  return props.activeThread?.lineNumber === thread.lineNumber &&
         String(props.activeThread?.blockId) === String(thread.blockId);
}

function formatDate(dateString: string) {
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

async function submitReply() {
  if (!props.activeThread || !replyText.value.trim() || submitting.value) return;

  submitting.value = true;
  try {
    const response = await fetch(`/posts/${props.activeThread.postId}/blocks/${props.activeThread.blockId}/threads`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        line_number: props.activeThread.lineNumber,
        body: replyText.value.trim(),
      }),
    });

    if (response.ok) {
      replyText.value = '';
      emit('thread-updated');
      // Reload page to get updated comments
      router.reload({ only: ['post'] });
    } else {
      const error = await response.json();
      console.error('Failed to submit reply:', error);
      alert('Failed to submit reply. Please try again.');
    }
  } catch (error) {
    console.error('Error submitting reply:', error);
    alert('Failed to submit reply. Please try again.');
  } finally {
    submitting.value = false;
  }
}

async function toggleResolve() {
  if (!props.activeThread || submitting.value) return;

  submitting.value = true;
  try {
    const newResolvedState = !props.activeThread.resolved;
    const response = await fetch(`/posts/${props.activeThread.postId}/blocks/${props.activeThread.blockId}/threads/resolve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        line_number: props.activeThread.lineNumber,
        resolved: newResolvedState,
      }),
    });

    if (response.ok) {
      emit('thread-resolved', { resolved: newResolvedState });
      router.reload({ only: ['post'] });
    } else {
      const error = await response.json();
      console.error('Failed to toggle resolve:', error);
      alert('Failed to update thread status. Please try again.');
    }
  } catch (error) {
    console.error('Error toggling resolve:', error);
    alert('Failed to update thread status. Please try again.');
  } finally {
    submitting.value = false;
  }
}

// Focus reply textarea when thread becomes active
watch(() => props.activeThread, async (newThread) => {
  if (newThread) {
    await nextTick();
    if (replyTextareaRef.value) {
      replyTextareaRef.value.focus();
    }
    // Load reactions for messages
    if (newThread.messages) {
      newThread.messages.forEach((msg: any) => {
        if (!reactions.value[msg.id]) {
          reactions.value[msg.id] = msg.reactions || {};
        }
        if (!userReactions.value[msg.id]) {
          userReactions.value[msg.id] = msg.user_reactions || [];
        }
      });
    }
  }
});

function hasReaction(messageId: number | string, emoji: string): boolean {
  return userReactions.value[String(messageId)]?.includes(emoji) || false;
}

function getReactionCount(messageId: number | string, emoji: string): number {
  return reactions.value[String(messageId)]?.[emoji] || 0;
}

async function toggleReaction(messageId: number | string, emoji: string) {
  const msgId = String(messageId);
  const hasReacted = hasReaction(messageId, emoji);
  
  try {
    const response = await fetch(`/api/comments/${messageId}/reactions`, {
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
        if (reactions.value[msgId]?.[emoji]) {
          reactions.value[msgId][emoji]--;
          if (reactions.value[msgId][emoji] === 0) {
            delete reactions.value[msgId][emoji];
          }
        }
        userReactions.value[msgId] = userReactions.value[msgId]?.filter((e: string) => e !== emoji) || [];
      } else {
        // Add reaction
        if (!reactions.value[msgId]) {
          reactions.value[msgId] = {};
        }
        reactions.value[msgId][emoji] = (reactions.value[msgId][emoji] || 0) + 1;
        if (!userReactions.value[msgId]) {
          userReactions.value[msgId] = [];
        }
        userReactions.value[msgId].push(emoji);
      }
    }
  } catch (error) {
    console.error('Failed to toggle reaction:', error);
  }
}

function showReactionPicker(messageId: number | string) {
  showReactionPickerFor.value = showReactionPickerFor.value === String(messageId) ? null : String(messageId);
}
</script>

