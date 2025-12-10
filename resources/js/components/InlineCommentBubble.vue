<template>
    <button
        v-if="hasComments"
        @click.stop="$emit('click')"
        class="inline-comment-bubble absolute -left-6 top-0 w-4 h-4 rounded-full bg-blue-500 hover:bg-blue-600 transition-colors flex items-center justify-center text-white text-xs font-semibold shadow-sm"
        :aria-label="`${commentCount} inline comment${commentCount > 1 ? 's' : ''} on line ${lineNumber}`"
        :title="`${commentCount} comment${commentCount > 1 ? 's' : ''}`"
    >
        {{ commentCount }}
    </button>
</template>

<script setup lang="ts">
defineProps<{
    lineNumber: number;
    commentCount: number;
    hasComments: boolean;
}>();

defineEmits<{
    click: [];
}>();
</script>

<style scoped>
.inline-comment-bubble {
    z-index: 10;
}
</style>


