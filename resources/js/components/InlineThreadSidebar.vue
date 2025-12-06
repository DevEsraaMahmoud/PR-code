<template>
  <!-- Desktop: Fixed Sidebar -->
  <aside
    v-if="!isMobile"
    class="hidden lg:block fixed right-0 top-0 h-screen w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-2xl overflow-y-auto z-40 transition-transform duration-300 ease-in-out"
    :class="{ 'translate-x-0': visible, 'translate-x-full': !visible }"
    role="complementary"
    aria-label="Inline comment threads"
  >
    <div class="p-4">
      <!-- Header -->
      <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-4 z-10 backdrop-blur-sm bg-opacity-95 dark:bg-opacity-95 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            Inline Comments
          </h2>
          <button
            @click="$emit('close')"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-all p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 border border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-700"
            aria-label="Close sidebar"
            title="Close sidebar (Esc)"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <div class="p-4">
        <!-- Empty State -->
      <div v-if="!activeThread" class="text-center py-12 text-gray-500 dark:text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <p class="text-sm">Click a line number to open an inline thread</p>
      </div>

      <!-- Active Thread -->
      <div v-else class="space-y-4">
        <!-- Thread Header -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-lg p-4 border border-blue-200 dark:border-gray-600 shadow-sm mb-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-blue-600 text-white text-xs font-bold">
                {{ activeThread.lineNumber }}
              </span>
              Line {{ activeThread.lineNumber }}
            </h3>
            <span
              v-if="activeThread.resolved"
              class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300 rounded-full shadow-sm"
            >
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              Resolved
            </span>
          </div>
          <p class="text-xs text-gray-600 dark:text-gray-400">
            Block ID: {{ activeThread.blockId }}
          </p>
        </div>

        <!-- Thread Messages -->
        <div class="space-y-3">
          <div
            v-for="message in activeThread.messages"
            :key="message.id"
            class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700"
          >
            <!-- Message Header -->
            <div class="flex items-start gap-2 mb-2">
              <img
                :src="getAvatarUrl(message.user?.avatar_url, message.user?.name, 32)"
                :alt="message.user?.name || 'User'"
                class="w-8 h-8 rounded-full flex-shrink-0"
                @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(message.user?.name); }"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ message.user?.name || 'Anonymous' }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(message.created_at) }}
                  </span>
                  <span v-if="message.edited_at" class="text-xs text-gray-400 italic">
                    (edited)
                  </span>
                </div>
                <!-- Message Content -->
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                  {{ message.text || message.body }}
                </p>
              </div>
            </div>

            <!-- Message Actions -->
            <div v-if="canEditMessage(message)" class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
              <button
                v-if="!message.editing"
                @click="startEdit(message)"
                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
              >
                Edit
              </button>
              <button
                @click="deleteMessage(message)"
                class="text-xs text-red-600 dark:text-red-400 hover:underline"
              >
                Delete
              </button>
            </div>

            <!-- Edit Form -->
            <div v-if="message.editing" class="mt-2">
              <textarea
                v-model="message.editText"
                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none"
                rows="3"
              ></textarea>
              <div class="flex items-center gap-2 mt-2">
                <button
                  @click="saveEdit(message)"
                  class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  Save
                </button>
                <button
                  @click="cancelEdit(message)"
                  class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Reply Form -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
          <textarea
            v-model="replyText"
            ref="replyTextareaRef"
            placeholder="Add a reply..."
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            rows="3"
            @keydown.ctrl.enter="submitReply"
            @keydown.meta.enter="submitReply"
          ></textarea>
          <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Press Ctrl+Enter to send</span>
            <button
              @click="submitReply"
              :disabled="!replyText.trim() || submitting"
              class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
            >
              {{ submitting ? 'Posting...' : 'Reply' }}
            </button>
          </div>
        </div>

        <!-- Thread Actions -->
        <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
          <button
            v-if="canResolve"
            @click="toggleResolve"
            :disabled="submitting"
            class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors"
            :class="activeThread.resolved
              ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50'
              : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50'"
          >
            {{ activeThread.resolved ? 'Unresolve' : 'Resolve' }}
          </button>
        </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Mobile: Slide-over Modal -->
  <Teleport to="body">
    <div
      v-if="isMobile && visible"
      class="fixed inset-0 z-50 lg:hidden"
      @click.self="$emit('close')"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
      
      <!-- Slide-over Panel -->
      <div
        class="absolute right-0 top-0 h-full w-full max-w-sm bg-white dark:bg-gray-800 shadow-xl overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sidebar-title"
      >
        <div class="p-4">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <h2 id="sidebar-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Inline Comments
            </h2>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              aria-label="Close sidebar"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Same content as desktop -->
          <!-- Empty State -->
          <div v-if="!activeThread" class="text-center py-12 text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="text-sm">Tap a line number to open an inline thread</p>
          </div>

          <!-- Active Thread (same as desktop) -->
          <div v-else class="space-y-4">
            <!-- Thread Header -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                  Line {{ activeThread.lineNumber }}
                </h3>
                <span
                  v-if="activeThread.resolved"
                  class="inline-flex items-center gap-1 px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full"
                >
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  Resolved
                </span>
              </div>
              <p class="text-xs text-gray-600 dark:text-gray-400">
                Block {{ activeThread.blockId }}
              </p>
            </div>

            <!-- Thread Messages -->
            <div class="space-y-3">
              <div
                v-for="message in activeThread.messages"
                :key="message.id"
                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700"
              >
                <!-- Message Header -->
                <div class="flex items-start gap-2 mb-2">
                  <img
                    :src="getAvatarUrl(message.user?.avatar_url, message.user?.name, 32)"
                    :alt="message.user?.name || 'User'"
                    class="w-8 h-8 rounded-full flex-shrink-0"
                    @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(message.user?.name); }"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ message.user?.name || 'Anonymous' }}
                      </span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatDate(message.created_at) }}
                      </span>
                      <span v-if="message.edited_at" class="text-xs text-gray-400 italic">
                        (edited)
                      </span>
                    </div>
                    <!-- Message Content -->
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                      {{ message.text || message.body }}
                    </p>
                  </div>
                </div>

                <!-- Message Actions -->
                <div v-if="canEditMessage(message)" class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                  <button
                    v-if="!message.editing"
                    @click="startEdit(message)"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteMessage(message)"
                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                  >
                    Delete
                  </button>
                </div>

                <!-- Edit Form -->
                <div v-if="message.editing" class="mt-2">
                  <textarea
                    v-model="message.editText"
                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none"
                    rows="3"
                  ></textarea>
                  <div class="flex items-center gap-2 mt-2">
                    <button
                      @click="saveEdit(message)"
                      class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                      Save
                    </button>
                    <button
                      @click="cancelEdit(message)"
                      class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reply Form -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
              <textarea
                v-model="replyText"
                ref="replyTextareaRefMobile"
                placeholder="Add a reply..."
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                rows="3"
                @keydown.ctrl.enter="submitReply"
                @keydown.meta.enter="submitReply"
              ></textarea>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Press Ctrl+Enter to send</span>
                <button
                  @click="submitReply"
                  :disabled="!replyText.trim() || submitting"
                  class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
                >
                  {{ submitting ? 'Posting...' : 'Reply' }}
                </button>
              </div>
            </div>

            <!-- Thread Actions -->
            <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
              <button
                v-if="canResolve"
                @click="toggleResolve"
                :disabled="submitting"
                class="flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                :class="activeThread.resolved
                  ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50'
                  : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50'"
              >
                {{ activeThread.resolved ? 'Unresolve' : 'Resolve' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 32);

const props = defineProps<{
  visible?: boolean;
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
      editing?: boolean;
      editText?: string;
    }>;
    resolved?: boolean;
  } | null;
  currentUserId?: number | string;
  canResolve?: boolean;
}>();

const emit = defineEmits<{
  close: [];
  'thread-updated': [];
  'thread-resolved': [{ resolved: boolean }];
}>();

const replyText = ref('');
const submitting = ref(false);
const replyTextareaRef = ref<HTMLTextAreaElement | null>(null);
const replyTextareaRefMobile = ref<HTMLTextAreaElement | null>(null);
const isMobile = ref(false);

const canResolve = computed(() => {
  return props.canResolve !== false && !!props.currentUserId;
});

function checkMobile() {
  isMobile.value = window.innerWidth < 1024;
}

function handleEscapeKey(event: KeyboardEvent) {
  if (event.key === 'Escape' && props.visible) {
    emit('close');
  }
}

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
  document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
  document.removeEventListener('keydown', handleEscapeKey);
});

function formatDate(dateString: string) {
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

function canEditMessage(message: any): boolean {
  return message.user?.id === props.currentUserId;
}

function startEdit(message: any) {
  message.editing = true;
  message.editText = message.text || message.body || '';
}

function cancelEdit(message: any) {
  message.editing = false;
  message.editText = '';
}

async function saveEdit(message: any) {
  if (!message.editText?.trim() || !props.activeThread) return;
  
  submitting.value = true;
  try {
    await router.patch(`/inline-comments/${message.id}`, {
      body: message.editText.trim(),
    }, {
      preserveScroll: true,
      onSuccess: () => {
        message.text = message.editText;
        message.body = message.editText;
        message.edited_at = new Date().toISOString();
        message.editing = false;
        emit('thread-updated');
      },
    });
  } catch (error) {
    console.error('Failed to update comment:', error);
  } finally {
    submitting.value = false;
  }
}

async function deleteMessage(message: any) {
  if (!confirm('Are you sure you want to delete this comment?')) return;
  
  submitting.value = true;
  try {
    await router.delete(`/comments/${message.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('thread-updated');
      },
    });
  } catch (error) {
    console.error('Failed to delete comment:', error);
  } finally {
    submitting.value = false;
  }
}

async function submitReply() {
  if (!replyText.value.trim() || !props.activeThread || submitting.value) return;
  
  submitting.value = true;
  const replyContent = replyText.value.trim();
  replyText.value = ''; // Clear immediately for better UX
  
  try {
    const endpoint = `/posts/${props.activeThread.postId}/blocks/${props.activeThread.blockId}/threads`;
    await router.post(endpoint, {
      line: props.activeThread.lineNumber,
      content: replyContent,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        emit('thread-updated');
      },
      onError: () => {
        // Restore text on error
        replyText.value = replyContent;
      },
    });
  } catch (error) {
    console.error('Failed to submit reply:', error);
    replyText.value = replyContent; // Restore text on error
  } finally {
    submitting.value = false;
  }
}

async function toggleResolve() {
  if (!props.activeThread || submitting.value) return;
  
  submitting.value = true;
  try {
    const newResolvedState = !props.activeThread.resolved;
    // Assuming there's an endpoint to resolve threads
    // Adjust endpoint based on your backend API
    await router.patch(`/posts/${props.activeThread.postId}/blocks/${props.activeThread.blockId}/threads/resolve`, {
      line: props.activeThread.lineNumber,
      resolved: newResolvedState,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        emit('thread-resolved', { resolved: newResolvedState });
        emit('thread-updated');
      },
    });
  } catch (error) {
    console.error('Failed to toggle resolve:', error);
  } finally {
    submitting.value = false;
  }
}

// Focus reply textarea when thread becomes active
watch(() => props.activeThread, async (newThread) => {
  if (newThread) {
    await nextTick();
    const textarea = isMobile.value ? replyTextareaRefMobile.value : replyTextareaRef.value;
    if (textarea) {
      textarea.focus();
    }
  }
});
</script>

<style scoped>
/* Transition for sidebar */
aside {
  transition: transform 0.3s ease-in-out;
}
</style>

