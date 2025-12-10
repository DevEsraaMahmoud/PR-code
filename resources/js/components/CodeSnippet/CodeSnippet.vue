<template>
  <div
    v-if="displayCode && displayCode.trim()"
    class="code-snippet bg-gray-900 rounded-lg border border-gray-700 overflow-hidden relative"
    :class="{
      'transition-all duration-300': expanding,
    }"
  >
    <!-- Toolbar -->
    <CodeToolbar
      :language="currentLanguage"
      :code="displayCode"
      :truncated="isTruncated"
      :inline-count="inlineCount"
      :loading="loading"
      @show-more="handleShowMore"
      @comments-click="handleCommentsClick"
    />

    <!-- Code Container -->
    <div
      class="code-container overflow-x-auto"
      :class="{
        'max-h-[8rem] overflow-y-hidden': isTruncated && !expanding,
        'transition-all duration-300': expanding,
      }"
    >
      <pre
        class="m-0 p-0 text-xs font-mono"
        :class="{
          'fade-in': expanding,
        }"
      >
        <code class="block">
          <CodeLine
            v-for="(line, index) in codeLines"
            :key="`line-${index + 1}`"
            :line-number="index + 1"
            :highlighted-content="line"
            :comment-count="getCommentCountForLine(index + 1)"
            @line-clicked="handleLineClick"
          />
        </code>
      </pre>
    </div>

    <!-- Fallback: View full post link if no blockId -->
    <div
      v-if="isTruncated && !blockId"
      class="px-3 py-2 border-t border-gray-700 bg-gray-800/30"
    >
      <Link
        :href="`/posts/${postId}`"
        class="text-xs text-blue-400 hover:text-blue-300 transition-colors"
        @click.stop
      >
        View full post →
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import Prism from '@/utils/prism-init';
import CodeLine from './CodeLine.vue';
import CodeToolbar from './CodeToolbar.vue';

const props = defineProps<{
  code: string;
  language?: string;
  truncated?: boolean;
  blockId?: number | null;
  inlineCount?: number;
  postId?: number | string;
  inlineCommentsByLine?: Record<number, any[]>;
}>();

const emit = defineEmits<{
  'line-clicked': [{ lineNumber: number; blockId: number | null }];
}>();

const displayCode = ref(props.code || '');
const isTruncated = ref(props.truncated || false);
const loading = ref(false);
const expanding = ref(false);
const inlineCommentsMap = ref<Record<number, any[]>>(props.inlineCommentsByLine || {});

const currentLanguage = computed(() => {
  return props.language || 'text';
});

// Split code into lines and highlight each line
const codeLines = computed(() => {
  if (!displayCode.value) return [];
  
  try {
    const lang = currentLanguage.value.toLowerCase();
    const grammar = Prism.languages[lang] || Prism.languages.text;
    
    // Highlight the entire code block
    const highlighted = Prism.highlight(displayCode.value, grammar, lang);
    
    // Split by line breaks, preserving HTML
    const lines = highlighted.split(/\r?\n/);
    
    // If truncated, only show first N lines
    if (isTruncated.value && !expanding.value) {
      const maxLines = 8; // Show first 8 lines in preview
      return lines.slice(0, maxLines);
    }
    
    return lines;
  } catch (error) {
    console.error('Error highlighting code:', error);
    // Fallback: return plain text lines
    return displayCode.value.split(/\r?\n/);
  }
});

function getCommentCountForLine(lineNumber: number): number {
  const comments = inlineCommentsMap.value[lineNumber];
  return comments ? comments.length : 0;
}

async function handleShowMore() {
  if (!props.blockId) {
    // No blockId, just expand locally if we have more code
    expanding.value = true;
    isTruncated.value = false;
    return;
  }

  if (loading.value) return;

  loading.value = true;
  expanding.value = true;

  try {
    const response = await fetch(`/code-blocks/${props.blockId}/full`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to load full code: ${response.statusText}`);
    }

    const data = await response.json();
    
    if (data.raw_code) {
      displayCode.value = data.raw_code;
      isTruncated.value = false;
    }
    
    if (data.inline_comments_by_line) {
      inlineCommentsMap.value = data.inline_comments_by_line;
    }
  } catch (error) {
    console.error('Error loading full code:', error);
    // Gracefully degrade - just expand what we have
    isTruncated.value = false;
  } finally {
    loading.value = false;
    setTimeout(() => {
      expanding.value = false;
    }, 300);
  }
}

function handleLineClick({ lineNumber }: { lineNumber: number }) {
  emit('line-clicked', {
    lineNumber,
    blockId: props.blockId || null,
  });
}

function handleCommentsClick() {
  // Scroll to first line with comments or emit event
  const firstLineWithComments = Object.keys(inlineCommentsMap.value)
    .map(Number)
    .sort((a, b) => a - b)[0];
  
  if (firstLineWithComments) {
    handleLineClick({ lineNumber: firstLineWithComments });
  }
}

// Watch for prop changes
watch(() => props.code, (newCode) => {
  if (newCode) {
    displayCode.value = newCode;
  }
});

watch(() => props.inlineCommentsByLine, (newComments) => {
  if (newComments) {
    inlineCommentsMap.value = newComments;
  }
}, { deep: true });
</script>

<style scoped>
.code-snippet {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}

.fade-in {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Prism.js theme adjustments for dark background */
:deep(.token) {
  color: inherit;
}

/* Ensure code lines are properly styled */
:deep(code) {
  display: block;
  padding: 0;
  margin: 0;
  background: transparent;
}
</style>


