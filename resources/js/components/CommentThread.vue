<template>
  <div class="comment-thread bg-[#1e1e1e] border-t border-[#3e3e42]">
    <!-- Thread Header -->
    <div class="px-4 py-3 border-b border-[#3e3e42]">
      <div class="flex items-center justify-between">
        <div>
          <h4 class="text-sm font-semibold text-[#cccccc]">
            Line {{ thread.lineNumber }}
          </h4>
          <p class="text-xs text-[#858585] mt-0.5">
            {{ thread.messages.length }} message{{ thread.messages.length !== 1 ? 's' : '' }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button
            v-if="canResolve"
            @click="handleToggleResolve"
            :disabled="resolving"
            class="px-3 py-1 text-xs rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :class="thread.resolved 
              ? 'bg-green-900/30 text-green-400 hover:bg-green-900/40' 
              : 'bg-yellow-900/30 text-yellow-400 hover:bg-yellow-900/40'"
          >
            {{ resolving ? '...' : (thread.resolved ? '✓ Resolved' : 'Mark Resolved') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Messages List -->
    <div class="max-h-96 overflow-y-auto">
      <div v-if="thread.messages.length === 0" class="px-4 py-8 text-center">
        <p class="text-sm text-[#858585]">No messages yet</p>
      </div>

      <div v-else class="divide-y divide-[#3e3e42]">
        <div
          v-for="(message, index) in thread.messages"
          :key="message.id || index"
          class="px-4 py-3"
        >
          <div class="flex items-start gap-3">
            <!-- Avatar -->
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
              :style="{ backgroundColor: getAvatarColor(message.user?.name || 'Anonymous') }"
            >
              {{ (message.user?.name || message.author?.name || 'A').charAt(0).toUpperCase() }}
            </div>

            <!-- Message Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-[#cccccc]">
                  {{ message.user?.name || message.author?.name || 'Anonymous' }}
                </span>
                <span class="text-xs text-[#858585]">
                  {{ formatDate(message.created_at) }}
                </span>
                <span
                  v-if="message.edited_at && message.edited_at !== message.created_at"
                  class="text-xs text-[#858585] italic"
                >
                  (edited)
                </span>
              </div>
              <div
                v-if="!message.editing"
                class="text-sm text-[#cccccc] whitespace-pre-wrap"
              >
                {{ message.body || message.text || message.content }}
              </div>
              <div v-else class="space-y-2">
                <textarea
                  v-model="editText"
                  class="w-full px-3 py-2 text-sm border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] focus:outline-none focus:ring-2 focus:ring-[#007acc] resize-none"
                  rows="3"
                  @keydown.ctrl.enter="saveEdit(message)"
                  @keydown.meta.enter="saveEdit(message)"
                ></textarea>
                <div class="flex items-center gap-2">
                  <button
                    @click="saveEdit(message)"
                    :disabled="!editText.trim() || saving"
                    class="px-3 py-1 text-xs bg-[#007acc] text-white rounded hover:bg-[#005a9e] disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ saving ? 'Saving...' : 'Save' }}
                  </button>
                  <button
                    @click="cancelEdit(message)"
                    class="px-3 py-1 text-xs bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e]"
                  >
                    Cancel
                  </button>
                </div>
              </div>

              <!-- Message Actions -->
              <div v-if="!message.editing" class="flex items-center gap-4 mt-2">
                <button
                  v-if="isAuthenticated"
                  @click="toggleReplyForm(message)"
                  class="text-xs text-[#858585] hover:text-[#007acc] transition-colors"
                >
                  {{ replyFormOpen === message.id ? 'Cancel' : 'Reply' }}
                </button>
                <button
                  v-if="canEdit(message)"
                  @click="startEdit(message)"
                  class="text-xs text-[#858585] hover:text-[#007acc] transition-colors"
                >
                  Edit
                </button>
                <button
                  v-if="canDelete(message)"
                  @click="handleDelete(message.id)"
                  class="text-xs text-[#858585] hover:text-red-400 transition-colors"
                >
                  Delete
                </button>
                <Reactions
                  :target-id="message.id"
                  :target-type="'comment'"
                  :initial-reactions="message.reactions || []"
                  @reaction-changed="handleReactionChanged"
                />
              </div>

              <!-- Reply Form -->
              <div v-if="replyFormOpen === message.id" class="mt-3 pt-3 border-t border-[#3e3e42]">
                <textarea
                  v-model="replyText"
                  ref="replyTextareaRef"
                  placeholder="Write a reply..."
                  class="w-full px-3 py-2 text-sm border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-2 focus:ring-[#007acc] resize-none"
                  rows="3"
                  @keydown.ctrl.enter="submitReply(message.id)"
                  @keydown.meta.enter="submitReply(message.id)"
                ></textarea>
                <div class="flex items-center justify-between mt-2">
                  <span class="text-xs text-[#858585]">Press Ctrl+Enter to submit</span>
                  <div class="flex items-center gap-2">
                    <button
                      @click="replyFormOpen = null; replyText = ''"
                      class="px-3 py-1 text-xs bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e]"
                    >
                      Cancel
                    </button>
                    <button
                      @click="submitReply(message.id)"
                      :disabled="!replyText.trim() || submitting"
                      class="px-3 py-1 text-xs bg-[#007acc] text-white rounded hover:bg-[#005a9e] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ submitting ? 'Posting...' : 'Reply' }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Nested Replies -->
              <div v-if="message.replies && message.replies.length > 0" class="mt-3 ml-6 space-y-3 border-l-2 border-[#3e3e42] pl-4">
                <CommentThread
                  :thread="{
                    lineNumber: thread.lineNumber,
                    blockId: thread.blockId,
                    postId: thread.postId,
                    messages: message.replies,
                    resolved: false,
                  }"
                  :current-user-id="currentUserId"
                  :can-resolve="false"
                  @reply="handleReply"
                  @edit="handleEdit"
                  @delete="handleDelete"
                  @react="handleReact"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Comment Form -->
    <div v-if="isAuthenticated" class="px-4 py-3 border-t border-[#3e3e42]">
      <textarea
        v-model="newCommentText"
        ref="newCommentTextareaRef"
        placeholder="Add a comment on line {{ thread.lineNumber }}..."
        class="w-full px-3 py-2 text-sm border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-2 focus:ring-[#007acc] resize-none"
        rows="3"
        @keydown.ctrl.enter="submitNewComment"
        @keydown.meta.enter="submitNewComment"
      ></textarea>
      <div class="flex items-center justify-between mt-2">
        <span class="text-xs text-[#858585]">Press Ctrl+Enter to submit</span>
        <button
          @click="submitNewComment"
          :disabled="!newCommentText.trim() || submitting"
          class="px-3 py-1 text-xs bg-[#007acc] text-white rounded hover:bg-[#005a9e] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ submitting ? 'Posting...' : 'Comment' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import Reactions from './Reactions.vue';

const props = defineProps<{
  thread: {
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    messages: any[];
    resolved: boolean;
  };
  currentUserId?: number;
  canResolve?: boolean;
}>();

const emit = defineEmits<{
  'reply': [commentId: number, body: string];
  'resolve': [];
  'unresolve': [];
  'edit': [comment: any];
  'delete': [commentId: number];
  'react': [commentId: number, reactionType: string];
}>();

const replyFormOpen = ref<number | null>(null);
const replyText = ref('');
const newCommentText = ref('');
const submitting = ref(false);
const resolving = ref(false);
const saving = ref(false);
const editText = ref('');
const editingMessage = ref<any>(null);
const replyTextareaRef = ref<HTMLTextAreaElement>();
const newCommentTextareaRef = ref<HTMLTextAreaElement>();

const isAuthenticated = computed(() => !!props.currentUserId);

// Optimistic UI: Track pending operations
const optimisticMessages = ref<any[]>([]);

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

function getAvatarColor(name: string): string {
  const colors = [
    '#007acc', '#4fc3f7', '#569cd6', '#9cdcfe',
    '#c586c0', '#dcdcaa', '#ce9178', '#d16969',
  ];
  const index = name.charCodeAt(0) % colors.length;
  return colors[index];
}

function canEdit(message: any): boolean {
  return props.currentUserId && (message.user_id === props.currentUserId || message.user?.id === props.currentUserId);
}

function canDelete(message: any): boolean {
  return props.currentUserId && (message.user_id === props.currentUserId || message.user?.id === props.currentUserId);
}

function toggleReplyForm(message: any) {
  if (replyFormOpen.value === message.id) {
    replyFormOpen.value = null;
    replyText.value = '';
  } else {
    replyFormOpen.value = message.id;
    nextTick(() => {
      replyTextareaRef.value?.focus();
    });
  }
}

function startEdit(message: any) {
  editingMessage.value = message;
  editText.value = message.body || message.text || message.content || '';
  message.editing = true;
}

function cancelEdit(message: any) {
  message.editing = false;
  editingMessage.value = null;
  editText.value = '';
}

async function saveEdit(message: any) {
  if (!editText.value.trim() || saving.value) return;
  
  saving.value = true;
  const originalText = message.body || message.text || message.content;
  
  // Optimistic update
  message.body = editText.value;
  message.text = editText.value;
  message.content = editText.value;
  
  try {
    emit('edit', { ...message, body: editText.value });
    // Parent should handle API call and update
    message.editing = false;
    editingMessage.value = null;
    editText.value = '';
  } catch (error) {
    // Rollback on error
    message.body = originalText;
    message.text = originalText;
    message.content = originalText;
    console.error('Failed to save edit:', error);
  } finally {
    saving.value = false;
  }
}

async function submitReply(parentId: number) {
  if (!replyText.value.trim() || submitting.value) return;
  
  submitting.value = true;
  const replyBody = replyText.value.trim();
  
  // Optimistic update
  const optimisticReply = {
    id: `temp-${Date.now()}`,
    body: replyBody,
    text: replyBody,
    content: replyBody,
    user_id: props.currentUserId,
    user: { id: props.currentUserId, name: 'You' },
    created_at: new Date().toISOString(),
    parent_id: parentId,
    pending: true,
  };
  
  // Add to thread messages optimistically
  const parentMessage = props.thread.messages.find(m => m.id === parentId);
  if (parentMessage) {
    if (!parentMessage.replies) {
      parentMessage.replies = [];
    }
    parentMessage.replies.push(optimisticReply);
  }
  
  try {
    emit('reply', parentId, replyBody);
    replyText.value = '';
    replyFormOpen.value = null;
  } catch (error) {
    // Rollback on error
    if (parentMessage && parentMessage.replies) {
      const index = parentMessage.replies.findIndex((r: any) => r.id === optimisticReply.id);
      if (index > -1) {
        parentMessage.replies.splice(index, 1);
      }
    }
    console.error('Failed to submit reply:', error);
  } finally {
    submitting.value = false;
  }
}

async function submitNewComment() {
  if (!newCommentText.value.trim() || submitting.value) return;
  
  submitting.value = true;
  const commentBody = newCommentText.value.trim();
  
  // Optimistic update
  const optimisticComment = {
    id: `temp-${Date.now()}`,
    body: commentBody,
    text: commentBody,
    content: commentBody,
    user_id: props.currentUserId,
    user: { id: props.currentUserId, name: 'You' },
    created_at: new Date().toISOString(),
    pending: true,
  };
  
  // Add to thread messages optimistically
  const updatedMessages = [...props.thread.messages, optimisticComment];
  
  try {
    // Emit to parent to handle API call
    // Parent should call API and update thread
    emit('reply', 0, commentBody); // Use 0 as parentId for top-level comment
    newCommentText.value = '';
  } catch (error) {
    console.error('Failed to submit comment:', error);
  } finally {
    submitting.value = false;
  }
}

function handleDelete(commentId: number) {
  if (confirm('Are you sure you want to delete this comment?')) {
    emit('delete', commentId);
  }
}

function handleReactionChanged(data: { targetId: number | string; reactions: any[] }) {
  const message = props.thread.messages.find(m => m.id === data.targetId);
  if (message) {
    message.reactions = data.reactions;
  }
}

function handleReact(commentId: number, reactionType: string) {
  emit('react', commentId, reactionType);
}

function handleReply(commentId: number, body: string) {
  emit('reply', commentId, body);
}

function handleEdit(comment: any) {
  emit('edit', comment);
}

async function handleToggleResolve() {
  if (resolving.value) return;
  
  resolving.value = true;
  const wasResolved = props.thread.resolved;
  
  try {
    if (wasResolved) {
      emit('unresolve');
    } else {
      emit('resolve');
    }
  } catch (error) {
    console.error('Failed to toggle resolve:', error);
  } finally {
    resolving.value = false;
  }
}
</script>

<style scoped>
.comment-thread {
  max-height: 600px;
  display: flex;
  flex-direction: column;
}
</style>
