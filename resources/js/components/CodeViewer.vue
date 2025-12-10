<template>
  <div
    ref="containerRef"
    class="code-viewer relative bg-[#1e1e1e] rounded-lg border border-[#3e3e42] overflow-hidden"
    :class="{ 'has-focus': hasFocus }"
    @keydown="handleKeyDown"
    tabindex="0"
    role="region"
    :aria-label="`Code viewer for ${snippet.language || 'text'}`"
  >
    <!-- Header with Copy Button -->
    <div class="flex items-center justify-between px-4 py-2 bg-[#252526] border-b border-[#3e3e42]">
      <div class="flex items-center gap-3">
        <span v-if="snippet.language && snippet.language !== 'text'" class="text-xs font-medium text-[#858585] uppercase">
          {{ snippet.language }}
        </span>
        <span class="text-xs text-[#858585]">
          {{ codeLines.length }} {{ codeLines.length === 1 ? 'line' : 'lines' }}
        </span>
        <span v-if="highlightedLines && highlightedLines.length > 0" class="text-xs text-yellow-400">
          {{ highlightedLines.length }} highlighted
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="canCopy"
          @click.stop="copyCode"
          class="flex items-center gap-1.5 px-3 py-1 text-xs text-[#858585] hover:text-[#cccccc] hover:bg-[#3e3e42] rounded transition-colors"
          :title="copied ? 'Copied!' : 'Copy code'"
          :aria-label="copied ? 'Code copied' : 'Copy code to clipboard'"
        >
          <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ copied ? 'Copied' : 'Copy' }}</span>
        </button>
        <button
          v-if="canOpenDiff"
          @click.stop="handleOpenDiff"
          class="flex items-center gap-1.5 px-3 py-1 text-xs text-[#858585] hover:text-[#007acc] hover:bg-[#3e3e42] rounded transition-colors"
          title="Open diff view"
          aria-label="Open diff view"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Diff</span>
        </button>
      </div>
    </div>

    <!-- Code Container -->
    <div
      ref="codeContainerRef"
      class="code-container overflow-x-auto relative"
      @click="handleContainerClick"
      @mouseleave="hoveredLine = null"
    >
      <div class="flex">
        <!-- Line Numbers Gutter -->
        <div
          ref="lineNumbersRef"
          class="line-numbers flex-shrink-0 bg-[#1e1e1e] text-right select-none border-r border-[#3e3e42]"
          role="group"
          aria-label="Line numbers"
        >
          <div
            v-for="(line, index) in codeLines"
            :key="`line-${index + 1}`"
            :data-line-number="index + 1"
            :ref="el => { if (el) lineRefs[index + 1] = el as HTMLElement }"
            class="line-number flex items-center justify-end px-3 py-0 text-[#858585] hover:text-[#cccccc] text-sm leading-6 min-h-[24px] cursor-pointer transition-colors relative group"
            :class="{
              'bg-[#094771]': selectedLine === index + 1 || keyboardSelectedLine === index + 1,
              'bg-yellow-900/20': hasCommentsOnLine(index + 1) && selectedLine !== index + 1,
              'has-comments': hasCommentsOnLine(index + 1),
            }"
            :style="getLineStyle(index + 1)"
            :tabindex="isAuthenticated ? 0 : -1"
            :aria-label="getLineAriaLabel(index + 1)"
            role="button"
            @click.stop="handleLineClick(index + 1)"
            @keydown.enter.prevent="handleLineClick(index + 1)"
            @keydown.space.prevent="handleLineClick(index + 1)"
            @mouseenter="hoveredLine = index + 1"
          >
            <span class="inline-flex items-center gap-1.5">
              <span>{{ index + 1 }}</span>
              <!-- Comment Indicator -->
              <span
                v-if="hasCommentsOnLine(index + 1)"
                class="inline-flex items-center justify-center min-w-[16px] h-[16px] px-1 rounded-full bg-yellow-500 hover:bg-yellow-400 text-white text-[10px] font-bold transition-colors"
                :title="`${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''} on this line`"
              >
                {{ getCommentCount(index + 1) }}
              </span>
            </span>
            <!-- Hover Tooltip for Comments -->
            <div
              v-if="hoveredLine === index + 1 && hasCommentsOnLine(index + 1)"
              class="absolute right-full mr-2 top-1/2 -translate-y-1/2 z-50 bg-[#252526] border border-[#3e3e42] rounded px-3 py-2 shadow-lg max-w-xs pointer-events-none"
            >
              <div class="text-xs text-[#cccccc] font-medium mb-1">
                Line {{ index + 1 }} - {{ getCommentCount(index + 1) }} comment{{ getCommentCount(index + 1) > 1 ? 's' : '' }}
              </div>
              <div v-if="getFirstCommentExcerpt(index + 1)" class="text-xs text-[#858585] line-clamp-2">
                {{ getFirstCommentExcerpt(index + 1) }}
              </div>
              <div class="text-xs text-[#858585] mt-1">
                by {{ getFirstCommentAuthor(index + 1) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Code Content -->
        <div
          ref="codeContentRef"
          class="code-content flex-1 px-4 py-0 text-[#cccccc] overflow-x-auto relative"
        >
          <pre class="m-0 p-0 font-mono text-sm leading-6" style="background: transparent;"><code
            ref="codeRef"
            class="block"
            v-html="highlightedCode"
          ></code></pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick, onUnmounted } from 'vue';
// @ts-ignore - prism-init is a JS file without type declarations
import Prism from '@/utils/prism-init';

const props = defineProps<{
  snippet: {
    id: number | string;
    language: string;
    content: string;
    versions?: any[];
  };
  highlightedLines?: number[];
  inlineCommentsIndex?: Record<string, any[]>; // { lineNumber: comments[] }
  isAuthenticated?: boolean;
  canCopy?: boolean;
  canOpenDiff?: boolean;
}>();

const emit = defineEmits<{
  'lineSelected': [{ lineNumber: number }];
  'copySucceeded': [];
  'openDiffRequested': [snippetId: number | string];
}>();

const containerRef = ref<HTMLElement>();
const codeContainerRef = ref<HTMLElement>();
const lineNumbersRef = ref<HTMLElement>();
const codeContentRef = ref<HTMLElement>();
const codeRef = ref<HTMLElement>();
const lineRefs: Record<number, HTMLElement> = {};

const selectedLine = ref<number | null>(null);
const keyboardSelectedLine = ref<number | null>(null);
const hoveredLine = ref<number | null>(null);
const hasFocus = ref(false);
const copied = ref(false);
const highlightedCode = ref<string>('');

const normalizedLanguage = computed(() => {
  const lang = (props.snippet.language || 'text').toLowerCase();
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
  if (!props.snippet.content) return [];
  return props.snippet.content.split(/\r?\n/);
});

const isAuthenticated = computed(() => props.isAuthenticated ?? true);

function hasCommentsOnLine(lineNumber: number): boolean {
  if (!props.inlineCommentsIndex) return false;
  const comments = props.inlineCommentsIndex[String(lineNumber)] || [];
  return Array.isArray(comments) ? comments.length > 0 : false;
}

function getCommentCount(lineNumber: number): number {
  if (!props.inlineCommentsIndex) return 0;
  const comments = props.inlineCommentsIndex[String(lineNumber)] || [];
  return Array.isArray(comments) ? comments.length : 0;
}

function getFirstCommentExcerpt(lineNumber: number): string {
  if (!props.inlineCommentsIndex) return '';
  const comments = props.inlineCommentsIndex[String(lineNumber)] || [];
  if (Array.isArray(comments) && comments.length > 0) {
    const firstComment = comments[0];
    const text = firstComment.body || firstComment.text || firstComment.content || '';
    return text.length > 50 ? text.substring(0, 50) + '...' : text;
  }
  return '';
}

function getFirstCommentAuthor(lineNumber: number): string {
  if (!props.inlineCommentsIndex) return '';
  const comments = props.inlineCommentsIndex[String(lineNumber)] || [];
  if (Array.isArray(comments) && comments.length > 0) {
    const firstComment = comments[0];
    return firstComment.user?.name || firstComment.author?.name || 'Anonymous';
  }
  return '';
}

function getLineStyle(lineNumber: number): Record<string, string> {
  const style: Record<string, string> = {};
  
  if (hasCommentsOnLine(lineNumber) && selectedLine.value !== lineNumber) {
    style['border-left-width'] = '3px';
    style['border-left-color'] = 'rgb(234 179 8)';
    style['background-color'] = 'rgb(113 63 18 / 0.25)';
    style['box-shadow'] = 'inset 3px 0 0 rgb(234 179 8 / 0.6)';
  } else if (selectedLine.value === lineNumber || keyboardSelectedLine.value === lineNumber) {
    style['border-left-width'] = '3px';
    style['border-left-color'] = 'rgb(0 122 204)';
    style['box-shadow'] = 'inset 3px 0 0 rgb(0 122 204 / 0.6)';
  }
  
  if (props.highlightedLines && props.highlightedLines.includes(lineNumber)) {
    style['background-color'] = 'rgb(0 122 204 / 0.1)';
  }
  
  return style;
}

function getLineAriaLabel(lineNumber: number): string {
  let label = `Line ${lineNumber}`;
  if (hasCommentsOnLine(lineNumber)) {
    label += `, ${getCommentCount(lineNumber)} comment${getCommentCount(lineNumber) > 1 ? 's' : ''}`;
  }
  return label;
}

function handleLineClick(lineNumber: number) {
  if (!isAuthenticated.value) {
    // Show login prompt or emit event
    return;
  }
  
  selectedLine.value = lineNumber;
  emit('lineSelected', { lineNumber });
  
  // Reset selection after a brief moment
  setTimeout(() => {
    if (selectedLine.value === lineNumber) {
      selectedLine.value = null;
    }
  }, 300);
}

function handleContainerClick(event: MouseEvent) {
  // Handle shift+click for line selection (copy selected lines)
  if (event.shiftKey && selectedLine.value) {
    // TODO: Implement multi-line selection
    return;
  }
}

function handleKeyDown(event: KeyboardEvent) {
  if (!isAuthenticated.value) return;
  
  const currentLine = keyboardSelectedLine.value || 1;
  let newLine: number | null = null;
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      newLine = Math.min(currentLine + 1, codeLines.value.length);
      keyboardSelectedLine.value = newLine;
      scrollToLine(newLine);
      break;
    case 'ArrowUp':
      event.preventDefault();
      newLine = Math.max(currentLine - 1, 1);
      keyboardSelectedLine.value = newLine;
      scrollToLine(newLine);
      break;
    case 'Enter':
      if (keyboardSelectedLine.value) {
        event.preventDefault();
        handleLineClick(keyboardSelectedLine.value);
      }
      break;
    case 'Escape':
      keyboardSelectedLine.value = null;
      selectedLine.value = null;
      break;
  }
}

function scrollToLine(lineNumber: number) {
  nextTick(() => {
    const lineEl = lineRefs[lineNumber];
    if (lineEl) {
      lineEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      lineEl.focus();
    }
  });
}

async function copyCode() {
  if (!props.snippet.content) return;
  
  try {
    await navigator.clipboard.writeText(props.snippet.content);
    copied.value = true;
    emit('copySucceeded');
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy code:', err);
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = props.snippet.content;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      copied.value = true;
      emit('copySucceeded');
      setTimeout(() => {
        copied.value = false;
      }, 2000);
    } catch (fallbackErr) {
      console.error('Fallback copy failed:', fallbackErr);
    }
    document.body.removeChild(textArea);
  }
}

function handleOpenDiff() {
  emit('openDiffRequested', props.snippet.id);
}

function updateHighlighting() {
  if (!props.snippet.content) {
    highlightedCode.value = '';
    return;
  }
  
  try {
    const lang = normalizedLanguage.value;
    const grammar = Prism.languages[lang] || Prism.languages.text;
    
    // Highlight the entire code block
    const highlighted = Prism.highlight(props.snippet.content, grammar, lang);
    
    // Split by line breaks and wrap each line
    const lines = highlighted.split(/\r?\n/);
    highlightedCode.value = lines.map((line, index) => {
      return `<span class="code-line" data-line="${index + 1}">${line || ' '}</span>`;
    }).join('\n');
  } catch (error) {
    console.error('Error highlighting code:', error);
    // Fallback: return plain text lines
    const lines = props.snippet.content.split(/\r?\n/);
    highlightedCode.value = lines.map((line, index) => {
      return `<span class="code-line" data-line="${index + 1}">${escapeHtml(line)}</span>`;
    }).join('\n');
  }
}

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Focus management
function handleFocus() {
  hasFocus.value = true;
}

function handleBlur() {
  hasFocus.value = false;
}

onMounted(() => {
  updateHighlighting();
  if (containerRef.value) {
    containerRef.value.addEventListener('focus', handleFocus);
    containerRef.value.addEventListener('blur', handleBlur);
  }
});

onUnmounted(() => {
  if (containerRef.value) {
    containerRef.value.removeEventListener('focus', handleFocus);
    containerRef.value.removeEventListener('blur', handleBlur);
  }
});

watch(() => props.snippet.content, () => {
  updateHighlighting();
});

watch(() => props.snippet.language, () => {
  updateHighlighting();
});
</script>

<style scoped>
.code-viewer {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.code-viewer:focus {
  outline: 2px solid rgb(0 122 204);
  outline-offset: -2px;
}

.code-container {
  max-width: 100%;
  background-color: #1e1e1e;
}

.line-number {
  min-height: 24px;
  background-color: #1e1e1e;
}

.line-number.has-comments {
  position: relative;
}

.code-content :deep(.code-line) {
  display: block;
  min-height: 24px;
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

.code-content :deep(.token.tag) {
  color: #569cd6;
}

.code-content :deep(.token.attr-name),
.code-content :deep(.token.selector .token.attr-name) {
  color: #ce9178;
}

.code-content :deep(.token.attr-value),
.code-content :deep(.token.string) {
  color: #ce9178;
}

.code-content :deep(.token.property) {
  color: #9cdcfe;
}

.code-content :deep(.token.keyword),
.code-content :deep(.token.atrule) {
  color: #c586c0;
}

.code-content :deep(.token.number),
.code-content :deep(.token.boolean),
.code-content :deep(.token.constant),
.code-content :deep(.token.symbol) {
  color: #569cd6;
}

.code-content :deep(.token.function),
.code-content :deep(.token.class-name) {
  color: #dcdcaa;
}

.code-content :deep(.token.operator),
.code-content :deep(.token.entity) {
  color: #d4d4d4;
}

.code-content :deep(.token.variable) {
  color: #9cdcfe;
}

.code-content :deep(.token.regex) {
  color: #d16969;
}

.code-content :deep(.token.builtin) {
  color: #4ec9b0;
}
</style>
