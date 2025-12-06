<template>
  <div
    v-if="code && code.trim()"
    class="code-snippet-compact relative bg-[#0b1220] rounded-lg border border-gray-700 overflow-hidden"
    :class="{ 
      'max-h-[220px]': isFeed,
      'text-[10px] sm:text-xs': isFeed,
      'rounded-lg sm:rounded-lg': true
    }"
  >
    <!-- Code Container -->
    <div
      class="code-container overflow-x-auto"
      :class="{ 'max-h-[220px] overflow-y-hidden': isFeed && !expanded }"
      :style="{ 'mask-image': isFeed && !expanded ? 'linear-gradient(to bottom, black 0%, black 85%, transparent 100%)' : 'none' }"
    >
      <pre class="m-0 p-0 text-xs font-mono leading-tight"><code class="block">
        <div
          v-for="(line, index) in codeLines"
          :key="`line-${index + 1}`"
          :data-line-number="index + 1"
          class="code-line flex items-start hover:bg-gray-800/40 transition-all border-l-2"
          :class="{
            'bg-blue-900/30 border-blue-500': selectedLine === index + 1,
            'border-yellow-500/70 bg-yellow-900/30': getCommentCount(index + 1) > 0 && selectedLine !== index + 1,
            'border-transparent hover:border-blue-500/50': getCommentCount(index + 1) === 0 && selectedLine !== index + 1,
          }"
          :style="getCommentCount(index + 1) > 0 && selectedLine !== index + 1 ? { 
            'border-left-width': '3px',
            'border-left-color': 'rgb(234 179 8 / 0.7)',
            'background-color': 'rgb(113 63 18 / 0.3)'
          } : {}"
        >
          <!-- Line Number Column -->
          <div
            class="line-number-column flex-shrink-0 px-3 py-1 text-gray-400 hover:text-gray-300 text-right select-none min-w-[3.5rem] cursor-pointer group relative"
            :aria-label="`Line ${index + 1}${getCommentCount(index + 1) > 0 ? `, ${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''}` : ''}`"
            role="button"
            tabindex="0"
            @click.stop="handleLineClick(index + 1)"
            @keydown.enter="handleLineClick(index + 1)"
            @keydown.space.prevent="handleLineClick(index + 1)"
          >
            <span class="inline-flex items-center gap-1.5">
              <span class="text-xs">{{ index + 1 }}</span>
              <!-- Comment Badge -->
              <span
                v-if="getCommentCount(index + 1) > 0"
                class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-full bg-yellow-500 hover:bg-yellow-400 text-white text-[10px] font-bold transition-colors shadow-md ring-2 ring-yellow-400/50"
                :aria-label="`${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''} on this line`"
                title="Click to view comments"
              >
                {{ getCommentCount(index + 1) }}
              </span>
              <!-- Hover indicator -->
              <span v-else class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-400 text-xs">+</span>
            </span>
          </div>
          
          <!-- Code Content -->
          <div
            class="code-content flex-1 px-4 py-1 text-gray-100 overflow-x-auto text-sm leading-relaxed"
            v-html="line"
          ></div>
        </div>
      </code></pre>
    </div>

    <!-- Expand Button (Feed only) -->
    <div
      v-if="isFeed && !expanded && codeLines.length > 8"
      class="absolute bottom-0 left-0 right-0 px-4 py-3 bg-gradient-to-t from-[#0b1220] via-[#0b1220]/95 to-transparent flex items-center justify-center z-10"
    >
      <button
        @click.stop="expanded = true"
        class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors bg-[#0b1220]/80 px-3 py-1.5 rounded-md border border-blue-500/30 hover:border-blue-400/50 hover:bg-[#0b1220]"
        aria-label="Expand code snippet"
      >
        Show full code ({{ codeLines.length }} lines) →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Prism from '@/utils/prism-init';

const props = defineProps<{
  code: string;
  language?: string;
  blockId?: number | string | null;
  postId?: number | string;
  inlineCommentsIndex?: Record<string, number>; // { lineNumber: commentCount }
  isFeed?: boolean; // If true, apply feed-specific styling (height limit, etc.)
}>();

const emit = defineEmits<{
  'line-clicked': [{ lineNumber: number; blockId: number | string | null; postId: number | string }];
}>();

const expanded = ref(false);
const selectedLine = ref<number | null>(null);

const normalizedLanguage = computed(() => {
  const lang = (props.language || 'text').toLowerCase();
  // Map common aliases
  const langMap: Record<string, string> = {
    'js': 'javascript',
    'ts': 'typescript',
    'py': 'python',
    'rb': 'ruby',
    'sh': 'bash',
    'zsh': 'bash',
  };
  return langMap[lang] || lang;
});

const codeLines = computed(() => {
  if (!props.code) return [];
  
  try {
    const lang = normalizedLanguage.value;
    const grammar = Prism.languages[lang] || Prism.languages.text;
    
    // Highlight the entire code block
    const highlighted = Prism.highlight(props.code, grammar, lang);
    
    // Split by line breaks, preserving HTML
    const lines = highlighted.split(/\r?\n/);
    
    return lines;
  } catch (error) {
    console.error('Error highlighting code:', error);
    // Fallback: return plain text lines
    return props.code.split(/\r?\n/).map(line => `<span>${escapeHtml(line)}</span>`);
  }
});

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function getCommentCount(lineNumber: number): number {
  if (!props.inlineCommentsIndex) return 0;
  const count = props.inlineCommentsIndex[String(lineNumber)] || 0;
  return typeof count === 'number' ? count : 0;
}

function handleLineClick(lineNumber: number) {
  selectedLine.value = lineNumber;
  emit('line-clicked', {
    lineNumber,
    blockId: props.blockId || null,
    postId: props.postId!,
  });
  
  // Reset selection after a brief moment
  setTimeout(() => {
    selectedLine.value = null;
  }, 300);
}

// Watch for prop changes
watch(() => props.code, () => {
  expanded.value = false;
});
</script>

<style scoped>
.code-snippet-compact {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}

.line-number-column {
  user-select: none;
}

.code-content :deep(.token) {
  color: inherit;
}

/* Prism.js theme adjustments for dark background */
.code-content :deep(.token.comment),
.code-content :deep(.token.prolog),
.code-content :deep(.token.doctype),
.code-content :deep(.token.cdata) {
  color: #6a9955;
}

.code-content :deep(.token.punctuation) {
  color: #d4d4d4;
}

.code-content :deep(.token.property),
.code-content :deep(.token.tag),
.code-content :deep(.token.boolean),
.code-content :deep(.token.number),
.code-content :deep(.token.constant),
.code-content :deep(.token.symbol),
.code-content :deep(.token.deleted) {
  color: #569cd6;
}

.code-content :deep(.token.selector),
.code-content :deep(.token.attr-name),
.code-content :deep(.token.string),
.code-content :deep(.token.char),
.code-content :deep(.token.builtin),
.code-content :deep(.token.inserted) {
  color: #ce9178;
}

.code-content :deep(.token.operator),
.code-content :deep(.token.entity),
.code-content :deep(.token.url),
.code-content :deep(.language-css .token.string),
.code-content :deep(.style .token.string) {
  color: #d4d4d4;
}

.code-content :deep(.token.atrule),
.code-content :deep(.token.attr-value),
.code-content :deep(.token.keyword) {
  color: #c586c0;
}

.code-content :deep(.token.function),
.code-content :deep(.token.class-name) {
  color: #dcdcaa;
}

.code-content :deep(.token.regex),
.code-content :deep(.token.important),
.code-content :deep(.token.variable) {
  color: #d16969;
}
</style>

