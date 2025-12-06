<template>
    <div
        v-if="selectedLine !== null"
        class="inline-comments-panel"
        :class="{
            'fixed bottom-0 left-0 right-0 lg:relative lg:bottom-auto lg:left-auto lg:right-auto': isMobile,
            'lg:w-96 lg:border-l lg:border-gray-700': !isMobile,
        }"
    >
        <!-- Mobile Header -->
        <div
            v-if="isMobile"
            class="bg-[#161b22] border-b border-gray-700 px-4 py-3 flex items-center justify-between"
        >
            <h3 class="text-sm font-semibold text-gray-200">Line {{ selectedLine }} Comments</h3>
            <button
                @click="$emit('close')"
                class="text-gray-400 hover:text-gray-200"
                aria-label="Close comments panel"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Desktop Header -->
        <div
            v-else
            class="bg-[#161b22] border-b border-gray-700 px-4 py-3"
        >
            <h3 class="text-sm font-semibold text-gray-200">Line {{ selectedLine }}</h3>
            <p class="text-xs text-gray-400 mt-1">{{ comments.length }} comment{{ comments.length !== 1 ? 's' : '' }}</p>
        </div>

        <!-- Comments List -->
        <div class="overflow-y-auto max-h-[calc(100vh-200px)] lg:max-h-[600px] p-4 space-y-4">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="bg-gray-800/50 rounded-lg p-3 border border-gray-700"
            >
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                            {{ comment.user?.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-200">{{ comment.user?.name || 'Anonymous' }}</p>
                            <p class="text-xs text-gray-400">{{ formatDate(comment.created_at) }}</p>
                        </div>
                    </div>
                    <button
                        v-if="canEditComment(comment)"
                        @click="$emit('edit', comment)"
                        class="text-gray-400 hover:text-gray-200 text-xs"
                        aria-label="Edit comment"
                    >
                        Edit
                    </button>
                </div>
                <p class="text-sm text-gray-300 whitespace-pre-wrap">{{ comment.body }}</p>
                <div v-if="comment.edited_at" class="text-xs text-gray-500 mt-1">
                    Edited {{ formatDate(comment.edited_at) }}
                </div>
                
                <!-- Replies -->
                <div v-if="comment.replies?.length" class="mt-3 ml-4 space-y-2 border-l-2 border-gray-700 pl-3">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="bg-gray-900/50 rounded p-2"
                    >
                        <div class="flex items-center space-x-2 mb-1">
                            <div class="w-5 h-5 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                {{ reply.user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <span class="text-xs font-medium text-gray-300">{{ reply.user?.name || 'Anonymous' }}</span>
                            <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                        </div>
                        <p class="text-xs text-gray-300 whitespace-pre-wrap">{{ reply.body }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Comment Form -->
            <div v-if="isAuthenticated" class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <form @submit.prevent="handleSubmit">
                    <textarea
                        v-model="newCommentBody"
                        placeholder="Add a comment..."
                        class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        rows="3"
                        :disabled="isSubmitting"
                    ></textarea>
                    <div class="flex items-center justify-end space-x-2 mt-2">
                        <button
                            type="button"
                            @click="handleCancel"
                            class="px-3 py-1 text-xs text-gray-400 hover:text-gray-200"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!newCommentBody.trim() || isSubmitting"
                        >
                            {{ isSubmitting ? 'Posting...' : 'Post Comment' }}
                        </button>
                    </div>
                </form>
            </div>

            <div v-else class="text-center py-4 text-sm text-gray-400">
                <a href="/login" class="text-blue-400 hover:text-blue-300">Sign in</a> to add a comment
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useWindowSize } from '@vueuse/core';

const props = defineProps<{
    selectedLine: number | null;
    comments: any[];
    postId: number;
    isAuthenticated?: boolean;
    currentUserId?: number;
}>();

const emit = defineEmits<{
    close: [];
    edit: [comment: any];
    'comment-added': [];
}>();

const { width } = useWindowSize();
const isMobile = computed(() => width.value < 1024);

const newCommentBody = ref('');
const isSubmitting = ref(false);

const canEditComment = (comment: any): boolean => {
    return props.isAuthenticated && props.currentUserId === comment.user?.id;
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
};

const handleSubmit = async () => {
    if (!newCommentBody.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    const commentBody = newCommentBody.value;
    
    // Optimistic update
    const optimisticComment = {
        id: Date.now(),
        body: commentBody,
        user: { id: props.currentUserId, name: 'You' },
        created_at: new Date().toISOString(),
        replies: [],
    };
    
    emit('comment-added');
    
    try {
        await router.post(`/posts/${props.postId}/inline-comments`, {
            post_id: props.postId,
            snippet_id: props.comments[0]?.snippet_id || null,
            start_line: props.selectedLine,
            end_line: props.selectedLine,
            body: commentBody,
            is_inline: true,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                newCommentBody.value = '';
                router.reload({ only: ['post'] });
            },
            onError: (errors) => {
                console.error('Failed to create comment:', errors);
                // Revert optimistic update would happen here
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } catch (error) {
        console.error('Error submitting comment:', error);
        isSubmitting.value = false;
    }
};

const handleCancel = () => {
    newCommentBody.value = '';
    emit('close');
};

watch(() => props.selectedLine, () => {
    newCommentBody.value = '';
});
</script>

<style scoped>
.inline-comments-panel {
    background: #0d1117;
    border-top: 1px solid #30363d;
    z-index: 50;
}

@media (min-width: 1024px) {
    .inline-comments-panel {
        max-height: 600px;
        overflow-y: auto;
    }
}
</style>

