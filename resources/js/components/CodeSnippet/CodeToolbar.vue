<template>
  <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-800/50 border-b border-gray-700">
    <!-- Language Tag -->
    <span
      v-if="language"
      class="text-xs font-medium text-gray-400 uppercase px-2 py-0.5 rounded bg-gray-700/50"
    >
      {{ language }}
    </span>

    <!-- Copy Button -->
    <button
      @click.stop="handleCopy"
      class="ml-auto text-xs text-gray-400 hover:text-gray-200 transition-colors px-2 py-1 rounded hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800"
      :aria-label="copied ? 'Copied!' : 'Copy code'"
      :title="copied ? 'Copied!' : 'Copy code'"
    >
      <svg
        v-if="!copied"
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
        />
      </svg>
      <svg
        v-else
        class="w-4 h-4 text-green-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 13l4 4L19 7"
        />
      </svg>
    </button>

    <!-- Show More Button -->
    <button
      v-if="truncated"
      @click.stop="handleShowMore"
      class="text-xs text-blue-400 hover:text-blue-300 transition-colors px-2 py-1 rounded hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800"
      :disabled="loading"
      :aria-label="loading ? 'Loading...' : 'Show more code'"
    >
      {{ loading ? 'Loading...' : 'Show more' }}
    </button>

    <!-- Inline Comments Badge -->
    <button
      v-if="inlineCount > 0"
      @click.stop="handleCommentsClick"
      class="text-xs text-gray-400 hover:text-gray-200 transition-colors px-2 py-1 rounded hover:bg-gray-700/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 flex items-center gap-1"
      :aria-label="`${inlineCount} inline comment${inlineCount !== 1 ? 's' : ''}`"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
        />
      </svg>
      <span>{{ inlineCount }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  language?: string;
  code?: string;
  truncated?: boolean;
  inlineCount?: number;
  loading?: boolean;
}>();

const emit = defineEmits<{
  'show-more': [];
  'comments-click': [];
}>();

const copied = ref(false);

function handleCopy() {
  if (!props.code) return;
  
  navigator.clipboard.writeText(props.code).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  }).catch((err) => {
    console.error('Failed to copy code:', err);
  });
}

function handleShowMore() {
  emit('show-more');
}

function handleCommentsClick() {
  emit('comments-click');
}
</script>


