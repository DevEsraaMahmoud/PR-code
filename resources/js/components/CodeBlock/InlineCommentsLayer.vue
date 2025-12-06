<template>
  <div class="inline-comments-layer">
    <!-- Desktop: Side-by-side layout -->
    <div class="hidden lg:grid lg:grid-cols-[1fr_320px] lg:gap-6">
      <!-- Code Viewer -->
      <div>
        <CodeViewer
          :code="code"
          :copyable="copyable"
          @line-click="handleLineClick"
        />
      </div>

      <!-- Inline Comments Sidebar -->
      <div class="sticky top-4 h-fit">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
            Inline Comments
          </h3>
          
          <div v-if="groupedComments.length === 0" class="text-sm text-gray-500 text-center py-8">
            Click on a line number to add a comment
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="group in groupedComments"
              :key="group.lineNumber"
              class="comment-group"
            >
              <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                  Line {{ group.lineNumber }}
                </span>
                <span class="text-xs text-gray-400">
                  ({{ group.comments.length }})
                </span>
              </div>
              <div class="space-y-2">
                <div
                  v-for="comment in group.comments"
                  :key="comment.id"
                  class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded text-sm"
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
                        <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                          {{ comment.user?.name || 'Anonymous' }}
                        </span>
                        <span class="text-xs text-gray-500">
                          {{ formatDate(comment.created_at) }}
                        </span>
                      </div>
                      <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ comment.text }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile: Collapsible panels -->
    <div class="lg:hidden space-y-4">
      <CodeViewer
        :code="code"
        :copyable="copyable"
        @line-click="handleLineClick"
      />

      <div
        v-for="group in groupedComments"
        :key="group.lineNumber"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
      >
        <button
          @click="togglePanel(group.lineNumber)"
          class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          :aria-expanded="openPanels.has(group.lineNumber)"
          :aria-controls="`panel-${group.lineNumber}`"
        >
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
            Line {{ group.lineNumber }} ({{ group.comments.length }})
          </span>
          <svg
            class="w-5 h-5 text-gray-400 transition-transform"
            :class="{ 'rotate-180': openPanels.has(group.lineNumber) }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div
          v-show="openPanels.has(group.lineNumber)"
          :id="`panel-${group.lineNumber}`"
          class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 space-y-3"
        >
          <div
            v-for="comment in group.comments"
            :key="comment.id"
            class="flex items-start gap-2"
          >
            <img
              :src="getAvatarUrl(comment.user?.avatar_url, comment.user?.name, 24)"
              :alt="comment.user?.name || 'User'"
              class="w-6 h-6 rounded-full flex-shrink-0"
              @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(comment.user?.name); }"
            />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                  {{ comment.user?.name || 'Anonymous' }}
                </span>
                <span class="text-xs text-gray-500">
                  {{ formatDate(comment.created_at) }}
                </span>
              </div>
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ comment.text }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Popover for adding new comments -->
    <InlineCommentPopover
      v-if="selectedLine !== null && popoverVisible"
      :visible="popoverVisible"
      :line-number="selectedLine!"
      :comments="getCommentsForLine(selectedLine!)"
      :position="popoverPosition"
      @close="closePopover"
      @submit-comment="handleSubmitComment"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import CodeViewer from './CodeViewer.vue';
import InlineCommentPopover from './InlineCommentPopover.vue';
import { router } from '@inertiajs/vue3';
import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 20);

const props = defineProps<{
  code: {
    language: string;
    content: string;
  };
  inlineComments?: any[];
  postId: number | string;
  copyable?: boolean;
  snippetId?: number | null; // Optional snippet ID if available
}>();

const emit = defineEmits<{
  'comment-added': [{ lineNumber: number; text: string }];
}>();

const popoverVisible = ref(false);
const selectedLine = ref<number | null>(null);
const popoverPosition = ref({ top: 0, left: 0 });
const openPanels = ref(new Set<number>());

const groupedComments = computed(() => {
  const groups = new Map<number, { lineNumber: number; comments: any[] }>();
  
  if (!props.inlineComments || !Array.isArray(props.inlineComments)) {
    return [];
  }
  
  props.inlineComments.forEach((comment) => {
    const lineNum = comment.line_number;
    if (!groups.has(lineNum)) {
      groups.set(lineNum, {
        lineNumber: lineNum,
        comments: [],
      });
    }
    groups.get(lineNum)!.comments.push(comment);
  });

  return Array.from(groups.values()).sort((a, b) => a.lineNumber - b.lineNumber);
});

function getCommentsForLine(lineNumber: number | null): any[] {
  if (!lineNumber || !props.inlineComments || !Array.isArray(props.inlineComments)) return [];
  return props.inlineComments.filter((c) => c.line_number === lineNumber);
}

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

function handleLineClick(lineNumber: number) {
  if (!lineNumber || lineNumber <= 0) {
    console.warn('Invalid line number:', lineNumber);
    return;
  }
  
  console.log('Line clicked:', lineNumber);
  selectedLine.value = lineNumber;
  
  // Calculate popover position (center it near the clicked line)
  // Try multiple selectors to find the line element
  const selectors = [
    `[data-line-number="${lineNumber}"]`,
    `.line-number[data-line-number="${lineNumber}"]`,
    `.line-overlay[data-line-number="${lineNumber}"]`
  ];
  
  let lineElement: Element | null = null;
  for (const selector of selectors) {
    lineElement = document.querySelector(selector);
    if (lineElement) {
      console.log('Found line element with selector:', selector);
      break;
    }
  }
  
  if (lineElement) {
    const rect = lineElement.getBoundingClientRect();
    popoverPosition.value = {
      top: rect.top + window.scrollY + rect.height / 2,
      left: rect.right + window.scrollX + 20,
    };
    
    // Adjust if popover would go off screen
    if (popoverPosition.value.left + 320 > window.innerWidth) {
      popoverPosition.value.left = rect.left + window.scrollX - 340;
    }
    
    // Ensure popover is visible on screen
    if (popoverPosition.value.top + 200 > window.innerHeight + window.scrollY) {
      popoverPosition.value.top = window.scrollY + window.innerHeight / 2 - 100;
    }
  } else {
    // Fallback position - center of viewport
    popoverPosition.value = {
      top: window.scrollY + window.innerHeight / 2 - 100,
      left: window.scrollX + window.innerWidth / 2 - 160,
    };
    console.warn('Line element not found, using fallback position');
  }
  
  popoverVisible.value = true;
  console.log('Popover visible:', popoverVisible.value, 'Position:', popoverPosition.value, 'Line:', selectedLine.value);
}

function closePopover() {
  popoverVisible.value = false;
  selectedLine.value = null;
}

function handleSubmitComment({ lineNumber, text }: { lineNumber: number; text: string }) {
  if (!lineNumber || !text.trim()) {
    console.error('Invalid comment data:', { lineNumber, text });
    return;
  }

  // Prepare data matching backend expectations
  const commentData: any = {
    start_line: lineNumber,
    end_line: lineNumber, // Single line comment
    body: text.trim(),
    is_inline: true,
  };

  // Add snippet_id if available
  if (props.snippetId) {
    commentData.snippet_id = props.snippetId;
  }

  console.log('Submitting inline comment:', commentData);

  router.post(
    `/posts/${props.postId}/inline-comments`,
    commentData,
    {
      preserveScroll: true,
      onSuccess: () => {
        emit('comment-added', { lineNumber, text });
        closePopover();
      },
      onError: (errors) => {
        console.error('Failed to add inline comment:', errors);
        // You might want to show a toast notification here
      },
    }
  );
}

function togglePanel(lineNumber) {
  if (openPanels.value.has(lineNumber)) {
    openPanels.value.delete(lineNumber);
  } else {
    openPanels.value.add(lineNumber);
  }
}

// Auto-open panels on mobile if there are comments
onMounted(() => {
  if (window.innerWidth < 1024) {
    groupedComments.value.forEach((group) => {
      openPanels.value.add(group.lineNumber);
    });
  }
});
</script>

