<template>
  <div class="code-viewer-container relative bg-gray-900 rounded-lg overflow-hidden border border-gray-700">
    <!-- Code Header -->
    <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
      <div class="flex items-center gap-2">
        <span class="text-xs font-medium text-gray-400 uppercase">{{ code.language }}</span>
        <span class="text-xs text-gray-500">{{ lines.length }} lines</span>
      </div>
      <button
        v-if="copyable"
        @click="copyCode"
        class="text-xs text-gray-400 hover:text-gray-200 transition-colors"
        :aria-label="`Copy ${code.language} code`"
      >
        {{ copied ? 'Copied!' : 'Copy' }}
      </button>
    </div>

    <!-- Code Content -->
    <div class="relative overflow-x-auto">
      <div class="flex">
        <!-- Line Numbers Column -->
        <div
          class="line-numbers flex-shrink-0 bg-gray-800/50 px-3 py-2 text-right select-none border-r border-gray-700"
          role="group"
          aria-label="Line numbers"
        >
          <div
            v-for="(line, index) in lines"
            :key="index"
            :data-line-number="index + 1"
            class="line-number text-gray-500 text-sm leading-6 min-h-[24px] cursor-pointer hover:bg-gray-700/30 transition-colors focus-within:bg-gray-700/50"
            :class="{ 'bg-blue-900/30': selectedLines.has(index + 1) }"
            :tabindex="0"
            @click="toggleLineSelection(index + 1)"
            @keydown.enter="toggleLineSelection(index + 1)"
            @keydown.space.prevent="toggleLineSelection(index + 1)"
            :aria-label="`Line ${index + 1}`"
            role="button"
          >
            {{ index + 1 }}
          </div>
        </div>

        <!-- Code Column -->
        <div class="code-content flex-1 relative" ref="codeContainerRef">
          <pre class="m-0 p-0"><code
            :class="`language-${normalizedLanguage}`"
            class="block px-4 py-2 text-sm leading-6"
            ref="codeRef"
          >{{ code.content }}</code></pre>

          <!-- Line Overlays for Click Targets -->
          <div class="absolute inset-0 pointer-events-none">
            <div
              v-for="(line, index) in lines"
              :key="index"
              :data-line-number="index + 1"
              class="line-overlay min-h-[24px] pointer-events-auto cursor-pointer hover:bg-blue-500/10 transition-colors"
              :class="{ 'bg-blue-500/20': selectedLines.has(index + 1) }"
              @click="handleLineClick(index + 1)"
              :aria-label="`Click to comment on line ${index + 1}`"
              role="button"
              tabindex="0"
              @keydown.enter="handleLineClick(index + 1)"
              @keydown.space.prevent="handleLineClick(index + 1)"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { highlightCode, normalizeLanguage } from '@/util/syntax';

const props = defineProps<{
  code: {
    language: string;
    content: string;
  };
  copyable?: boolean;
  selectableLines?: boolean;
}>();

const emit = defineEmits<{
  'line-click': [lineNumber: number];
  'line-select': [selectedLines: number[]];
}>();

const codeRef = ref(null);
const codeContainerRef = ref(null);
const copied = ref(false);
const selectedLines = ref(new Set());

const normalizedLanguage = computed(() => {
  return normalizeLanguage(props.code.language || 'text');
});

const lines = computed(() => {
  if (!props.code.content) return [];
  return props.code.content.split('\n');
});

function toggleLineSelection(lineNumber) {
  if (!props.selectableLines) return;
  
  if (selectedLines.value.has(lineNumber)) {
    selectedLines.value.delete(lineNumber);
  } else {
    selectedLines.value.add(lineNumber);
  }
  emit('line-select', Array.from(selectedLines.value));
}

function handleLineClick(lineNumber) {
  emit('line-click', lineNumber);
}

async function copyCode() {
  if (!props.code.content) return;
  
  try {
    await navigator.clipboard.writeText(props.code.content);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy code:', err);
  }
}

onMounted(async () => {
  await nextTick();
  if (codeRef.value) {
    // Small delay to ensure Prism is fully initialized
    setTimeout(() => {
      highlightCode(codeRef.value);
    }, 0);
  }
});

watch(
  () => [props.code.content, props.code.language],
  async () => {
    await nextTick();
    if (codeRef.value) {
      // Small delay to ensure DOM is updated
      setTimeout(() => {
        highlightCode(codeRef.value);
      }, 0);
    }
  },
  { deep: true }
);
</script>

<style scoped>
.code-viewer-container {
  font-family: 'Fira Code', 'Consolas', 'Monaco', 'Courier New', monospace;
}

.line-number {
  user-select: none;
}

.line-overlay {
  display: block;
  width: 100%;
}
</style>

