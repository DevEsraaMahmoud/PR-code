<template>
    <div class="w-80 bg-white border-l border-gray-200 p-4 overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comments</h3>
        <div v-if="comments.length === 0" class="text-gray-500 text-sm">
            No comments yet. Select lines to add a comment.
        </div>
        <div v-else class="space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="border-b border-gray-200 pb-4 last:border-b-0"
            >
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ comment.user.name }}</p>
                        <p class="text-xs text-gray-500">
                            Lines {{ comment.start_line }}-{{ comment.end_line }}
                        </p>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ formatDate(comment.created_at) }}
                    </span>
                </div>
                <p class="text-sm text-gray-700 mb-2">{{ comment.body }}</p>
                <button
                    v-if="isAuthenticated"
                    @click="replyToComment(comment.id)"
                    class="text-xs text-blue-600 hover:text-blue-700"
                >
                    Reply
                </button>
                <div v-if="comment.replies && comment.replies.length > 0" class="ml-4 mt-2 space-y-2">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="border-l-2 border-gray-200 pl-3"
                    >
                        <p class="text-xs font-medium text-gray-900">{{ reply.user.name }}</p>
                        <p class="text-xs text-gray-700">{{ reply.body }}</p>
                        <span class="text-xs text-gray-400">{{ formatDate(reply.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

const props = defineProps<{
    comments: any[];
    isAuthenticated?: boolean;
}>();

const emit = defineEmits<{
    reply: [commentId: number];
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

const replyToComment = (commentId: number) => {
    emit('reply', commentId);
};
</script>

