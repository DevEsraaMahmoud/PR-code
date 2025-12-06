<template>
  <article
    class="post-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow cursor-pointer"
    @click="navigateToPost"
  >
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img
            :src="getAvatarUrl(post.author?.avatar_url, post.author?.name, 40)"
            :alt="post.author?.name || 'Author'"
            class="w-10 h-10 rounded-full"
            @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(post.author?.name); }"
          />
          <div>
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-900 dark:text-gray-100">
                {{ post.author?.name || 'Anonymous' }}
              </span>
              <span v-if="post.author?.handle" class="text-sm text-gray-500">
                @{{ post.author.handle }}
              </span>
            </div>
            <span class="text-xs text-gray-500">
              {{ formatDate(post.created_at) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="px-6 py-4">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
        {{ post.title }}
      </h2>
      
      <!-- Rich text excerpt -->
      <div
        class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3 prose prose-sm dark:prose-invert max-w-none"
        v-html="truncateHtml(post.body || post.body_html || '')"
      ></div>

      <!-- Render blocks in order: text blocks and code blocks -->
      <div v-if="postBlocks && postBlocks.length > 0" class="space-y-3 mb-4">
        <template v-for="(block, index) in postBlocks" :key="`block-${index}`">
          <!-- Text Block -->
          <div
            v-if="block.type === 'text'"
            class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap"
            v-html="truncateHtml(block.content || '')"
          ></div>
          
          <!-- Code Block -->
          <div
            v-else-if="block.type === 'code'"
            class="mb-4"
            @click.stop
          >
            <CodeSnippetCompact
              :code="block.content || ''"
              :language="block.language || 'text'"
              :block-id="block.id"
              :post-id="post.id"
              :inline-comments-index="getInlineCommentsIndex(block.id)"
              :is-feed="true"
              @line-clicked="handleLineClick"
            />
          </div>
        </template>
      </div>

      <!-- Fallback: Code preview (for old format) -->
      <div
        v-else-if="codePreview"
        class="mb-4 -mx-2 sm:mx-0"
        @click.stop
      >
        <CodeSnippetCompact
          :code="codePreview"
          :language="codeLanguage"
          :block-id="codeBlockId"
          :post-id="post.id"
          :inline-comments-index="codeInlineCommentsIndex"
          :is-feed="true"
          @line-clicked="handleLineClick"
        />
      </div>
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-3 flex-wrap">
          <!-- Likes -->
          <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400" title="Likes">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                clip-rule="evenodd"
              />
            </svg>
            <span>{{ post.stats?.likes || 0 }}</span>
          </div>
          
          <!-- Inline Comments Count -->
          <div 
            v-if="inlineCommentsCount > 0"
            class="flex items-center gap-1 text-sm text-yellow-600 dark:text-yellow-400 font-medium" 
            title="Inline comments on code lines"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>{{ inlineCommentsCount }}</span>
          </div>
          
          <!-- General Comments Count -->
          <div 
            class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400" 
            title="General comments"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>{{ generalCommentsCount }}</span>
          </div>
          
          <!-- Lines Count (if code exists) -->
          <div 
            v-if="totalLinesCount > 0"
            class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-500" 
            title="Total lines of code"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            <span>{{ totalLinesCount }} lines</span>
          </div>
          
          <!-- Views -->
          <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400" title="Views">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>{{ post.stats?.views || 0 }}</span>
          </div>
        </div>
        <Link
          :href="`/posts/${post.id}`"
          class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium"
          @click.stop
        >
          Read more →
        </Link>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';
import CodeSnippetCompact from '@/Components/CodeSnippetCompact.vue';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 40);

const page = usePage();

const props = defineProps<{
  post: {
    id: number | string;
    author?: {
      id: number | string;
      name: string;
      avatar_url?: string;
      handle?: string;
    };
    title: string;
    body?: string;
    body_html?: string;
    blocks?: Array<{
      id: number | string;
      type: 'text' | 'code';
      content: string;
      language?: string;
    }>;
    code?: {
      language: string;
      content: string;
    };
    code_preview?: string;
    code_preview_meta?: {
      language?: string;
      truncated?: boolean;
      block_id?: number;
      inline_count?: number;
    };
    inlineIndex?: Record<string, Record<string, number>>; // { blockId: { lineNumber: commentCount } }
    stats?: {
      likes: number;
      comments: number;
      views: number;
    };
    created_at: string;
  };
}>();

const isAuthenticated = computed(() => {
  return !!(page.props as any).auth?.user;
});

// Post blocks (new format)
const postBlocks = computed(() => {
  if (Array.isArray(props.post.blocks) && props.post.blocks.length > 0) {
    return props.post.blocks;
  }
  // Fallback: create blocks from old format
  const blocks: any[] = [];
  if (props.post.body || props.post.body_html) {
    blocks.push({
      id: `text-${props.post.id}`,
      type: 'text',
      content: props.post.body || props.post.body_html || '',
    });
  }
  if (props.post.code?.content || props.post.code_preview) {
    blocks.push({
      id: props.post.code_preview_meta?.block_id || `code-${props.post.id}`,
      type: 'code',
      content: props.post.code?.content || props.post.code_preview || '',
      language: props.post.code?.language || props.post.code_preview_meta?.language || 'text',
    });
  }
  return blocks;
});

// Code preview data - support both old format (code.content) and new format (code_preview)
const codePreview = computed(() => {
  return props.post.code_preview || props.post.code?.content || '';
});

const codeLanguage = computed(() => {
  return props.post.code_preview_meta?.language || props.post.code?.language || 'text';
});

const codeBlockId = computed(() => {
  return props.post.code_preview_meta?.block_id || null;
});

const codeInlineCommentsIndex = computed(() => {
  if (!props.post.inlineIndex) return {};
  // If inlineIndex is provided, use it
  // Format: { blockId: { lineNumber: commentCount } }
  return props.post.inlineIndex[String(codeBlockId.value)] || {};
});

// Calculate total inline comments count from inlineIndex
const inlineCommentsCount = computed(() => {
  if (!props.post.inlineIndex) return 0;
  let total = 0;
  Object.values(props.post.inlineIndex).forEach((blockIndex: any) => {
    if (typeof blockIndex === 'object') {
      Object.values(blockIndex).forEach((count: any) => {
        total += typeof count === 'number' ? count : 0;
      });
    }
  });
  return total;
});

// Calculate general comments count (excluding inline)
const generalCommentsCount = computed(() => {
  const totalComments = props.post.stats?.comments || 0;
  return Math.max(0, totalComments - inlineCommentsCount.value);
});

// Calculate total lines count from code blocks
const totalLinesCount = computed(() => {
  let lines = 0;
  postBlocks.value.forEach((block: any) => {
    if (block.type === 'code' && block.content) {
      lines += block.content.split('\n').length;
    }
  });
  if (lines === 0 && codePreview.value) {
    lines = codePreview.value.split('\n').length;
  }
  return lines;
});

function getInlineCommentsIndex(blockId: number | string): Record<string, number> {
  if (!props.post.inlineIndex) return {};
  return props.post.inlineIndex[String(blockId)] || {};
}

function handleLineClick({ lineNumber, blockId, postId }: { lineNumber: number; blockId: number | string | null; postId: number | string }) {
  // On feed, clicking a line should navigate to the post detail page
  // The detail page will handle opening the thread sidebar
  router.visit(`/posts/${postId}`, {
    data: { line: lineNumber, blockId },
  });
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

function truncateHtml(html, maxLength = 200) {
  if (!html) return '';
  // Strip HTML tags for length calculation
  const text = html.replace(/<[^>]*>/g, '');
  if (text.length <= maxLength) return html;
  
  // Simple truncation (for production, use a proper HTML truncation library)
  return html.substring(0, maxLength) + '...';
}


function navigateToPost() {
  router.visit(`/posts/${props.post.id}`);
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

