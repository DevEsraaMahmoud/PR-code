<template>
  <div
    v-if="code && code.trim()"
    class="code-snippet-compact relative bg-[#1e1e1e] rounded-lg border border-[#3e3e42] overflow-hidden mx-auto"
    :class="{ 
      'max-h-[180px]': isFeed,
      'text-[9px]': isFeed,
      'rounded-lg sm:rounded-lg': true,
      'max-w-full': isFeed,
      'max-w-4xl': !isFeed,
    }"
  >
    <!-- Code Header with Copy Button -->
    <div class="flex items-center justify-between px-3 py-2 bg-[#252526] border-b border-[#3e3e42]">
      <div class="flex items-center gap-2">
        <span v-if="language && language !== 'text'" class="text-[10px] font-medium text-[#858585] uppercase">
          {{ language }}
        </span>
        <span class="text-[10px] text-[#858585]">
          {{ codeLines.length }} {{ codeLines.length === 1 ? 'line' : 'lines' }}
        </span>
      </div>
      <button
        @click.stop="copyCode"
        class="flex items-center gap-1.5 px-2 py-1 text-[10px] text-[#858585] hover:text-[#cccccc] hover:bg-[#3e3e42] rounded transition-colors"
        :title="copied ? 'Copied!' : 'Copy code'"
      >
        <svg v-if="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ copied ? 'Copied' : 'Copy' }}</span>
      </button>
    </div>

    <!-- Code Container -->
    <div
      class="code-container overflow-x-auto"
      :class="{ 'max-h-[180px] overflow-y-hidden': isFeed && !expanded }"
      :style="{ 'mask-image': isFeed && !expanded ? 'linear-gradient(to bottom, black 0%, black 70%, rgba(0,0,0,0.5) 85%, transparent 100%)' : 'none' }"
    >
      <pre class="m-0 p-0 font-mono" :class="{ 'text-[9px]': isFeed, 'text-xs': !isFeed }" style="line-height: 1.2;"><code class="block">
        <div
          v-for="(line, index) in codeLines"
          :key="`line-${index + 1}`"
          :data-line-number="index + 1"
          class="code-line flex items-start hover:bg-[#2a2d2e] transition-all relative"
          :class="{
            'bg-[#094771]': selectedLine === index + 1,
            'bg-[#3f3f00]': getCommentCount(index + 1) > 0 && selectedLine !== index + 1,
            'has-comments': getCommentCount(index + 1) > 0,
          }"
          :style="getCommentCount(index + 1) > 0 && selectedLine !== index + 1 ? { 
            'border-left-width': '3px',
            'border-left-color': 'rgb(234 179 8)',
            'background-color': 'rgb(113 63 18 / 0.25)',
            'box-shadow': 'inset 3px 0 0 rgb(234 179 8 / 0.6)'
          } : selectedLine === index + 1 ? {
            'border-left-width': '3px',
            'border-left-color': 'rgb(0 122 204)',
            'box-shadow': 'inset 3px 0 0 rgb(0 122 204 / 0.6)'
          } : {}"
        >
          <!-- Line Number Column -->
          <div
            class="line-number-column flex-shrink-0 px-3 py-0 text-[#858585] hover:text-[#cccccc] text-right select-none min-w-[3rem] cursor-pointer group relative bg-[#1e1e1e] border-r border-[#3e3e42]"
            style="line-height: 1.2; font-size: 0.75rem;"
            :aria-label="`Line ${index + 1}${getCommentCount(index + 1) > 0 ? `, ${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''}` : ''}`"
            role="button"
            tabindex="0"
            @click.stop="handleLineClick(index + 1)"
            @keydown.enter="handleLineClick(index + 1)"
            @keydown.space.prevent="handleLineClick(index + 1)"
          >
            <span class="inline-flex items-center gap-1">
              <span style="font-size: 0.75rem;">{{ index + 1 }}</span>
              <!-- Comment Indicator Dot -->
              <span
                v-if="getCommentCount(index + 1) > 0"
                class="inline-flex items-center justify-center min-w-[14px] h-[14px] px-0.5 rounded-full bg-yellow-500 hover:bg-yellow-400 text-white text-[8px] font-bold transition-colors shadow-sm"
                :aria-label="`${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''} on this line`"
                title="Click to view comments"
              >
                {{ getCommentCount(index + 1) }}
              </span>
              <!-- Small indicator dot for lines with comments -->
              <span
                v-if="getCommentCount(index + 1) > 0"
                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-yellow-500/80 rounded-r"
                :aria-label="`Line has comments`"
              ></span>
              <!-- Hover indicator -->
              <span v-else class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-400 text-xs">+</span>
            </span>
          </div>
          
          <!-- Code Content -->
          <div
            class="code-content flex-1 px-3 py-0 text-[#cccccc] overflow-x-auto"
            :class="{
              'text-[9px]': isFeed,
              'text-xs': !isFeed,
            }"
            style="line-height: 1.2; white-space: pre; word-break: normal;"
            v-html="line"
          ></div>
        </div>
      </code></pre>
    </div>

    <!-- Expand Button (Feed only) -->
    <div
      v-if="isFeed && !expanded && codeLines.length > 6"
      class="absolute bottom-0 left-0 right-0 px-4 py-2.5 bg-gradient-to-t from-[#1e1e1e] via-[#1e1e1e]/98 to-transparent flex items-center justify-center z-10 pointer-events-none"
    >
      <button
        @click.stop="expanded = true"
        class="text-[10px] text-[#007acc] hover:text-[#4fc3f7] font-medium transition-colors bg-[#1e1e1e]/90 px-3 py-1 rounded border border-[#007acc]/40 hover:border-[#007acc]/60 hover:bg-[#252526] pointer-events-auto shadow-lg"
        aria-label="Expand code snippet"
      >
        Show more ({{ codeLines.length - 6 }} more lines) ↓
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
const copied = ref(false);

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

async function copyCode() {
  try {
    await navigator.clipboard.writeText(props.code);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy code:', err);
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = props.code;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      copied.value = true;
      setTimeout(() => {
        copied.value = false;
      }, 2000);
    } catch (fallbackErr) {
      console.error('Fallback copy failed:', fallbackErr);
    }
    document.body.removeChild(textArea);
  }
}

// Watch for prop changes
watch(() => props.code, () => {
  expanded.value = false;
});
</script>

<style scoped>
.code-snippet-compact {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.code-container {
  max-width: 100%;
  background-color: #1e1e1e;
}

.code-line {
  min-height: 1.2rem;
  background-color: #1e1e1e;
}

.code-line:hover {
  background-color: #2a2d2e;
}

.code-line.has-comments {
  position: relative;
}

.code-line.has-comments::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background-color: rgb(234 179 8);
  opacity: 0.8;
  z-index: 1;
}

.line-number-column {
  user-select: none;
  background-color: #1e1e1e;
}

.code-content :deep(.token) {
  color: inherit;
}

/* Prism.js theme - VS Code Dark+ style */
.code-content :deep(.token.comment),
.code-content :deep(.token.prolog),
.code-content :deep(.token.doctype),
.code-content :deep(.token.cdata) {
  color: #6a9955;
  font-style: italic;
}

.code-content :deep(.token.punctuation) {
  color: #d4d4d4;
}

/* Tags - Light Blue */
.code-content :deep(.token.tag) {
  color: #569cd6;
}

/* Attributes - Orange */
.code-content :deep(.token.attr-name),
.code-content :deep(.token.selector .token.attr-name) {
  color: #ce9178;
}

/* Attribute Values - White/Default */
.code-content :deep(.token.attr-value),
.code-content :deep(.token.string) {
  color: #ce9178;
}

/* URLs - Underlined */
.code-content :deep(.token.url),
.code-content :deep(.token.string a),
.code-content :deep(a[href]) {
  text-decoration: underline;
  color: #ce9178;
}

/* Property names */
.code-content :deep(.token.property) {
  color: #9cdcfe;
}

/* Keywords - Purple */
.code-content :deep(.token.keyword),
.code-content :deep(.token.atrule) {
  color: #c586c0;
}

/* Numbers, Booleans, Constants - Light Blue */
.code-content :deep(.token.number),
.code-content :deep(.token.boolean),
.code-content :deep(.token.constant),
.code-content :deep(.token.symbol) {
  color: #569cd6;
}

/* Functions - Yellow */
.code-content :deep(.token.function),
.code-content :deep(.token.class-name) {
  color: #dcdcaa;
}

/* Operators */
.code-content :deep(.token.operator),
.code-content :deep(.token.entity) {
  color: #d4d4d4;
}

/* Variables */
.code-content :deep(.token.variable) {
  color: #9cdcfe;
}

/* Regex */
.code-content :deep(.token.regex) {
  color: #d16969;
}

/* Builtin */
.code-content :deep(.token.builtin) {
  color: #4ec9b0;
}

/* Inserted/Deleted */
.code-content :deep(.token.inserted) {
  color: #ce9178;
}

.code-content :deep(.token.deleted) {
  color: #569cd6;
}
</style>

