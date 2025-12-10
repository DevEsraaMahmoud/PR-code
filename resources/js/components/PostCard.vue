<template>
<article

    class="post-card bg-[#252526] rounded-lg border border-[#3e3e42] hover:border-[#007acc] transition-all cursor-pointer mb-4"

    @click="navigateToPost"
>
<!-- Header -->
<div class="px-4 py-3 border-b border-[#3e3e42]">
<div class="flex items-center gap-3">
<img

          :src="getAvatarUrl(post.author?.avatar_url, post.author?.name, 40)"

          :alt="post.author?.name || 'Author'"

          class="w-10 h-10 rounded-full flex-shrink-0"

          @error="(e) => { (e.target as HTMLImageElement).src = generateFallbackAvatar(post.author?.name); }"

        />
<div class="flex-1 min-w-0">
<div class="flex items-center gap-2">
<span class="font-semibold text-[#cccccc] truncate">

              {{ post.author?.name || 'Anonymous' }}
</span>
<span v-if="post.author?.handle" class="text-sm text-[#858585] truncate">

              @{{ post.author.handle }}
</span>
<span class="text-[#858585]">·</span>
<span class="text-xs text-[#858585]">

              {{ formatDate(post.created_at) }}
</span>
</div>
</div>
<!-- Options Menu -->
<div class="relative" @click.stop>
<button

            @click.stop="showOptions = !showOptions"

            class="p-1.5 rounded hover:bg-[#3e3e42] transition-colors text-[#858585] hover:text-[#cccccc]"

            title="More options"
>
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
</svg>
</button>
<!-- Dropdown Menu -->
<div

            v-if="showOptions"

            class="absolute right-0 top-full mt-1 bg-[#252526] border border-[#3e3e42] rounded shadow-lg z-50 min-w-[180px] py-1"

            @click.stop
>
<button

              @click="openInTab"

              class="w-full text-left px-4 py-2 text-sm text-[#cccccc] hover:bg-[#2a2d2e] transition-colors flex items-center gap-2"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
</svg>

              Open in new tab
</button>
<Link

              :href="`/posts/${post.id}`"

              class="block w-full text-left px-4 py-2 text-sm text-[#cccccc] hover:bg-[#2a2d2e] transition-colors flex items-center gap-2"

              @click.stop="showOptions = false"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
</svg>

              Open in new page
</Link>
<button

              @click="copyLink"

              class="w-full text-left px-4 py-2 text-sm text-[#cccccc] hover:bg-[#2a2d2e] transition-colors flex items-center gap-2"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
</svg>

              Copy link
</button>
</div>
</div>
</div>
</div>
<!-- Content -->
<div class="px-4 py-3">
<h2 class="text-lg font-semibold text-[#cccccc] mb-2 hover:text-[#007acc] transition-colors">

        {{ post.title }}
</h2>
<!-- Render blocks in order: text blocks and code blocks -->
<div v-if="postBlocks && postBlocks.length > 0" class="space-y-3">
<template v-for="(block, index) in postBlocks" :key="`block-${index}`">
<!-- Text Block -->
<div

            v-if="block.type === 'text'"

            class="text-[#cccccc] text-sm whitespace-pre-wrap leading-relaxed"

            v-html="truncateHtml(getBlockContent(block), 300)"
></div>
<!-- Code Block - Compact preview with expand -->
<div

            v-else-if="block.type === 'code'"

            class="my-3 -mx-4"

            @click.stop
>
<div

              class="relative px-4"

              :data-block-id="block.id"
>
<!-- Compact container: collapse by default -->
<div

                class="overflow-hidden rounded-md border border-[#3e3e42] bg-[#1e1e1f]"

                :style="isExpandedBlock(block.id) ? '' : compactStyle"
>
<CodeSnippetCompact

                  :code="getBlockContent(block) || ''"

                  :language="block.language || 'text'"

                  :block-id="block.id"

                  :post-id="post.id"

                  :inline-comments-index="getInlineCommentsIndex(block.id)"

                  :is-feed="true"

                  @line-clicked="handleLineClick"

                />
</div>
<!-- Gradient + Show more button only when collapsed and long -->
<div

                v-if="!isExpandedBlock(block.id) && getBlockLineCount(block) > compactMaxLines"

                class="absolute left-0 right-0 bottom-0 flex justify-center items-end pointer-events-none"

                style="height: 56px;"
>
<div style="pointer-events: auto;" class="mb-3">
<button

                    @click.stop="toggleExpandBlock(block.id)"

                    class="px-4 py-1 rounded-md bg-[#007acc] text-white text-sm shadow"
>

                    Show more
</button>
</div>
</div>
</div>
</div>
</template>
</div>
<!-- Fallback: Code preview (for old format) -->
<div

        v-else-if="codePreview"

        class="my-3 -mx-4"

        @click.stop
>
<div class="relative px-4" :data-block-id="codeBlockId">
<div

            class="overflow-hidden rounded-md border border-[#3e3e42] bg-[#1e1e1f]"

            :style="isExpandedBlock(codeBlockId) ? '' : compactStyle"
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
<div

            v-if="!isExpandedBlock(codeBlockId) && (codePreview.split('\n').length > compactMaxLines)"

            class="absolute left-0 right-0 bottom-0 flex justify-center items-end pointer-events-none"

            style="height: 56px;"
>
<div style="pointer-events: auto;" class="mb-3">
<button

                @click.stop="toggleExpandBlock(codeBlockId)"

                class="px-4 py-1 rounded-md bg-[#007acc] text-white text-sm shadow"
>

                Show more
</button>
</div>
</div>
</div>
</div>
<!-- Fallback: Text only -->
<div

        v-else-if="post.body || post.body_html"

        class="text-[#cccccc] text-sm whitespace-pre-wrap leading-relaxed line-clamp-4"

        v-html="truncateHtml(getPostBodyText(post), 300)"
></div>
</div>
<!-- Footer - Social Media Style -->
<div class="px-4 py-3 border-t border-[#3e3e42]">
<div class="flex items-center justify-between">
<div class="flex items-center gap-6">
<!-- Likes -->
<button

            class="flex items-center gap-1.5 text-sm text-[#858585] hover:text-red-400 transition-colors group"

            title="Likes"

            @click.stop
>
<svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
</svg>
<span class="font-medium">{{ post.stats?.likes || 0 }}</span>
</button>
<!-- Comments -->
<button

            class="flex items-center gap-1.5 text-sm text-[#858585] hover:text-[#007acc] transition-colors group"

            title="Comments"

            @click.stop
>
<svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
</svg>
<span class="font-medium">{{ (post.stats?.comments || 0) + inlineCommentsCount }}</span>
</button>
<!-- Inline Comments Badge -->
<div 

            v-if="inlineCommentsCount > 0"

            class="flex items-center gap-1.5 text-sm text-yellow-400 font-medium" 

            title="Inline comments on code"
>
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
<span>{{ inlineCommentsCount }}</span>
</div>
<!-- Code Lines Badge -->
<div 

            v-if="totalLinesCount > 0"

            class="flex items-center gap-1.5 text-sm text-gray-500" 

            title="Lines of code"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
</svg>
<span>{{ totalLinesCount }}</span>
</div>
</div>
<div class="flex items-center gap-3">
<!-- Share Button -->
<button

            class="flex items-center gap-1 text-sm text-[#858585] hover:text-[#007acc] transition-colors"

            title="Share"

            @click.stop="sharePost"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
</svg>
<span class="text-xs">Share</span>
</button>
<!-- Save Button -->
<button

            class="flex items-center gap-1 text-sm transition-colors"

            :class="isSaved ? 'text-yellow-400' : 'text-[#858585] hover:text-yellow-400'"

            title="Save for later"

            @click.stop="toggleSave"
>
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
</svg>
<span class="text-xs">Save</span>
</button>
<!-- View Post Link -->
<Link

            :href="`/posts/${post.id}`"

            class="text-sm text-[#007acc] hover:text-[#4fc3f7] font-medium transition-colors"

            @click.stop
>

            View →
</Link>
</div>
</div>
</div>
</article>
</template>
<script setup lang="ts">

import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';

import { Link, router, usePage } from '@inertiajs/vue3';

import { getAvatarUrl, generateDataUriAvatar } from '@/utils/avatar';

import CodeSnippetCompact from '@/components/CodeSnippetCompact.vue';

const generateFallbackAvatar = (name?: string) => generateDataUriAvatar(name, 40);

const page = usePage();

const showOptions = ref(false);

const isSaved = ref(false);

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

// Post blocks (new format)

const postBlocks = computed(() => {

  // Check if blocks array exists

  if (Array.isArray(props.post.blocks) && props.post.blocks.length > 0) {

    return props.post.blocks.map((block: any) => ({

      ...block,

      content: getBlockContent(block), // Ensure content is a string

    }));

  }

  // Check if body is an array of blocks

  if (Array.isArray(props.post.body) && props.post.body.length > 0) {

    return props.post.body.map((block: any, index: number) => {

      // If block has content property, ensure it's a string

      let content = '';

      if (block.content) {

        content = typeof block.content === 'string' 

          ? block.content 

          : (Array.isArray(block.content) ? block.content.join('\n') : String(block.content));

      }

      return {

        id: block.id || `${block.type}-${index + 1}`,

        type: block.type || 'text',

        content: content,

        language: block.language || 'text',

      };

    });

  }

  // Fallback: create blocks from old format

  const blocks: any[] = [];

  if (props.post.body || props.post.body_html) {

    const bodyText = typeof props.post.body === 'string' 

      ? props.post.body 

      : (props.post.body_html || '');

    if (bodyText) {

      blocks.push({

        id: `text-${props.post.id}`,

        type: 'text',

        content: bodyText,

      });

    }

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

function getBlockContent(block: any): string {

  if (!block || !block.content) return '';

  // If content is already a string, return it

  if (typeof block.content === 'string') {

    return block.content;

  }

  // If content is an array, join it

  if (Array.isArray(block.content)) {

    return block.content.join('\n');

  }

  // If content is an object, try to extract text

  if (typeof block.content === 'object') {

    return JSON.stringify(block.content);

  }

  // Fallback: convert to string

  return String(block.content);

}

function getPostBodyText(post: any): string {

  // If body is an array, extract text blocks

  if (Array.isArray(post.body)) {

    return post.body

      .filter((block: any) => block.type === 'text')

      .map((block: any) => getBlockContent(block))

      .join('\n\n');

  }

  // Otherwise use body_html or body as string

  return post.body_html || post.body || '';

}

function truncateHtml(html: any, maxLength = 200): string {

  if (!html) return '';

  // Convert to string if needed

  const htmlString = typeof html === 'string' ? html : String(html);

  // Strip HTML tags for length calculation

  const text = htmlString.replace(/<[^>]*>/g, '');

  if (text.length <= maxLength) return htmlString;

  // Simple truncation (for production, use a proper HTML truncation library)

  const truncated = htmlString.substring(0, maxLength);

  // Try to truncate at word boundary

  const lastSpace = truncated.lastIndexOf(' ');

  if (lastSpace > maxLength * 0.8) {

    return truncated.substring(0, lastSpace) + '...';

  }

  return truncated + '...';

}


function navigateToPost() {

  router.visit(`/posts/${props.post.id}`);

}

function openInTab() {

  showOptions.value = false;

  // Prepare full post data with all necessary fields

  const fullPostData = {

    ...props.post,

    // Ensure we have user object (for PostTabContent)

    user: props.post.user || props.post.author,

    // Preserve snippets if they exist in the original data

    snippets: props.post.snippets,

    // Preserve blocks and body array

    blocks: props.post.blocks,

    body: props.post.body,

    // Preserve inline comments index

    inlineIndex: props.post.inlineIndex || props.post.inline_comments_index,

  };

  // Dispatch custom event to AppLayout

  if (typeof window !== 'undefined') {

    const event = new CustomEvent('open-post-in-tab', {

      detail: {

        postId: props.post.id,

        postData: fullPostData,

        title: props.post.title || `Post ${props.post.id}`,

      },

    });

    window.dispatchEvent(event);

  }

}

function copyLink() {

  const url = `${window.location.origin}/posts/${props.post.id}`;

  navigator.clipboard.writeText(url).then(() => {

    showOptions.value = false;

    // You could show a toast notification here

  });

}

function sharePost() {

  if (navigator.share) {

    navigator.share({

      title: props.post.title,

      text: props.post.title,

      url: `${window.location.origin}/posts/${props.post.id}`,

    }).catch(err => console.log('Error sharing:', err));

  } else {

    // Fallback: copy link

    copyLink();

  }

}

function toggleSave() {

  isSaved.value = !isSaved.value;

  // TODO: Implement API call to save/unsave post

  // router.post(`/posts/${props.post.id}/save`, { saved: isSaved.value });

}

// Compact snippet state & helpers

const expandedBlocks = ref<Record<string, boolean>>({});

const compactMaxLines = 8;

const compactLineHeightPx = 20; // approx line height inside snippet - adjust if needed

const compactPaddingPx = 24; // combined vertical paddings inside snippet container

const compactStyle = computed(() => {

  const maxHeight = compactMaxLines * compactLineHeightPx + compactPaddingPx;

  return `max-height: ${maxHeight}px;`;

});

function isExpandedBlock(id: number | string | null) {

  if (!id) return false;

  return !!expandedBlocks.value[String(id)];

}

function toggleExpandBlock(id: number | string | null) {

  if (!id) return;

  expandedBlocks.value[String(id)] = !expandedBlocks.value[String(id)];

  if (expandedBlocks.value[String(id)]) {

    // scroll into view for expanded block

    nextTick(() => {

      const el = document.querySelector(`[data-block-id="${id}"]`);

      if (el) (el as HTMLElement).scrollIntoView({ behavior: 'smooth', block: 'center' });

    });

  }

}

function getBlockLineCount(block: any) {

  if (!block || !block.content) return 0;

  return String(block.content).split('\n').length;

}

// Close options menu when clicking outside

let handleClickOutside: ((e: MouseEvent) => void) | null = null;

onMounted(() => {

  handleClickOutside = (e: MouseEvent) => {

    if (showOptions.value && !(e.target as Element).closest('.relative')) {

      showOptions.value = false;

    }

  };

  document.addEventListener('click', handleClickOutside);

});

onUnmounted(() => {

  if (handleClickOutside) {

    document.removeEventListener('click', handleClickOutside);

  }

});
</script>
<style scoped>

.line-clamp-3 {

  display: -webkit-box;

  -webkit-line-clamp: 3;

  -webkit-box-orient: vertical;

  overflow: hidden;

}

/* small visual polish for compact snippets (optional) */

.post-card .code-compact-gradient {

  background: linear-gradient(to top, rgba(30,30,31,1), rgba(30,30,31,0));

}
</style>