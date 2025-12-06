<template>
    <div
        v-if="comments.length > 0"
        class="absolute right-0 top-0 flex items-center space-x-1"
        :style="{ top: `${(startLine - 1) * lineHeight}px` }"
    >
        <div
            class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full shadow-lg cursor-pointer hover:bg-blue-700 transition-colors"
            @click="showComments"
            :title="`${comments.length} comment${comments.length > 1 ? 's' : ''}`"
        >
            {{ comments.length }}
        </div>
        <div
            v-if="showTooltip"
            class="absolute right-0 top-full mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 p-3 z-50"
            @click.stop
        >
            <div class="text-xs font-semibold text-gray-900 mb-2">
                Comments on lines {{ startLine }}-{{ endLine }}
            </div>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="text-xs text-gray-700 border-b border-gray-100 pb-2 last:border-b-0"
                >
                    <div class="font-medium text-gray-900 mb-1">{{ comment.user.name }}</div>
                    <div class="line-clamp-2">{{ comment.body }}</div>
                </div>
            </div>
            <button
                @click="openFullComments"
                class="mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium"
            >
                View all comments →
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    comments: any[];
    startLine: number;
    endLine: number;
    lineHeight?: number;
}>();

const emit = defineEmits<{
    'view-comments': [];
}>();

const showTooltip = ref(false);
const lineHeight = props.lineHeight || 24;

const showComments = () => {
    showTooltip.value = !showTooltip.value;
};

const openFullComments = () => {
    emit('view-comments');
    showTooltip.value = false;
};

// Close tooltip when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.inline-annotation')) {
        showTooltip.value = false;
    }
};

if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

