<template>
  <div
    class="code-line group relative flex items-start gap-2 px-3 py-0.5 hover:bg-gray-800/30 transition-colors cursor-pointer focus-within:bg-gray-800/40"
    :class="{
      'bg-blue-900/20': hasComments,
      'border-l-2 border-blue-500': hasComments,
    }"
    @click.stop="handleClick"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
    :tabindex="0"
    :aria-label="`Line ${lineNumber}${hasComments ? `, ${commentCount} comment${commentCount !== 1 ? 's' : ''}` : ''}`"
    role="button"
  >
    <!-- Line Number -->
    <span
      class="text-xs text-gray-500 select-none font-mono min-w-[3rem] text-right pr-2"
      :aria-hidden="true"
    >
      {{ lineNumber }}
    </span>

    <!-- Code Content -->
    <code
      class="flex-1 text-xs font-mono text-gray-300 overflow-x-auto"
      v-html="highlightedContent"
    ></code>

    <!-- Comment Indicator -->
    <div
      v-if="hasComments"
      class="flex items-center gap-1 text-xs text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity"
      :aria-hidden="true"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
        />
      </svg>
      <span>{{ commentCount }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  lineNumber: number;
  highlightedContent: string;
  commentCount?: number;
}>();

const emit = defineEmits<{
  'line-clicked': [{ lineNumber: number }];
}>();

const hasComments = computed(() => {
  return (props.commentCount || 0) > 0;
});

const commentCount = computed(() => {
  return props.commentCount || 0;
});

function handleClick() {
  emit('line-clicked', { lineNumber: props.lineNumber });
}
</script>

<style scoped>
.code-line:focus {
  outline: 2px solid #3b82f6;
  outline-offset: -2px;
}
</style>

