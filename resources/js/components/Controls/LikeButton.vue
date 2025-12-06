<template>
  <button
    @click="handleClick"
    :disabled="pending"
    class="like-button flex items-center gap-2 px-3 py-1.5 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="[
      isLiked
        ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30'
        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
    ]"
    :aria-label="isLiked ? 'Unlike this post' : 'Like this post'"
    :aria-pressed="isLiked"
  >
    <svg
      class="w-5 h-5 transition-transform"
      :class="{ 'scale-110': isLiked }"
      fill="currentColor"
      viewBox="0 0 20 20"
    >
      <path
        fill-rule="evenodd"
        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
        clip-rule="evenodd"
      />
    </svg>
    <span class="text-sm font-medium">{{ displayCount }}</span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
// @ts-ignore - JS file, no type declarations
import { usePostStore } from '@/Composables/usePostState';

const props = defineProps<{
  postId: number | string;
  initialLiked?: boolean;
  initialCount?: number;
}>();

const emit = defineEmits<{
  liked: [];
  unliked: [];
}>();

const postStore = usePostStore();

const isLiked = computed(() => {
  return postStore.isLiked(props.postId) || props.initialLiked;
});

const displayCount = computed(() => {
  const optimisticStats = postStore.getOptimisticStats(props.postId, {
    likes: props.initialCount,
    comments: 0,
  });
  return optimisticStats.likes;
});

const pending = computed(() => {
  return postStore.pendingLikes.has(props.postId);
});

function handleClick() {
  if (pending.value) return;
  
  const wasLiked = isLiked.value;
  postStore.toggleLike(props.postId, props.initialLiked);
  
  if (wasLiked) {
    emit('unliked');
  } else {
    emit('liked');
  }
}
</script>

