<template>
    <div class="general-comments bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Comments</h2>
        
        <!-- Comments List -->
        <div v-if="comments.length === 0" class="text-gray-500 text-center py-8">
            No comments yet. Be the first to comment!
        </div>
        
        <div v-else class="space-y-4 mb-6">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
            >
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                            {{ comment.user?.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ comment.user?.name || 'Anonymous' }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            v-if="isAuthenticated && canEditComment(comment)"
                            @click="handleEdit(comment)"
                            class="text-xs text-gray-600 hover:text-gray-900"
                            aria-label="Edit comment"
                        >
                            Edit
                        </button>
                        <button
                            v-if="isAuthenticated && canDeleteComment(comment)"
                            @click="handleDelete(comment.id)"
                            class="text-xs text-red-600 hover:text-red-900"
                            aria-label="Delete comment"
                        >
                            Delete
                        </button>
                    </div>
                </div>
                
                <p class="text-sm text-gray-700 whitespace-pre-wrap mb-2">{{ comment.body }}</p>
                
                <div v-if="comment.edited_at" class="text-xs text-gray-500 mb-2">
                    Edited {{ formatDate(comment.edited_at) }}
                </div>
                
                <!-- Replies -->
                <div v-if="comment.replies?.length" class="mt-3 ml-4 space-y-2 border-l-2 border-gray-300 pl-3">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="bg-white rounded p-2"
                    >
                        <div class="flex items-center space-x-2 mb-1">
                            <div class="w-6 h-6 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                {{ reply.user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <span class="text-xs font-medium text-gray-700">{{ reply.user?.name || 'Anonymous' }}</span>
                            <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                        </div>
                        <p class="text-xs text-gray-600 whitespace-pre-wrap">{{ reply.body }}</p>
                    </div>
                </div>
                
                <!-- Reply Button -->
                <button
                    v-if="isAuthenticated"
                    @click="showReplyForm = showReplyForm === comment.id ? null : comment.id"
                    class="mt-2 text-xs text-blue-600 hover:text-blue-700"
                >
                    {{ showReplyForm === comment.id ? 'Cancel' : 'Reply' }}
                </button>
                
                <!-- Reply Form -->
                <form
                    v-if="showReplyForm === comment.id"
                    @submit.prevent="handleReply(comment.id)"
                    class="mt-3 ml-4"
                >
                    <textarea
                        v-model="replyBodies[comment.id]"
                        placeholder="Write a reply..."
                        class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        rows="2"
                        :disabled="isSubmitting"
                    ></textarea>
                    <div class="flex items-center justify-end space-x-2 mt-2">
                        <button
                            type="button"
                            @click="showReplyForm = null"
                            class="px-3 py-1 text-xs text-gray-600 hover:text-gray-900"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!replyBodies[comment.id]?.trim() || isSubmitting"
                        >
                            {{ isSubmitting ? 'Posting...' : 'Post Reply' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Add Comment Form -->
        <div v-if="isAuthenticated" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add a Comment</h3>
            <form @submit.prevent="handleSubmit">
                <textarea
                    v-model="newCommentBody"
                    placeholder="Write your comment..."
                    class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    rows="4"
                    :disabled="isSubmitting"
                ></textarea>
                <div class="flex items-center justify-end mt-3">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!newCommentBody.trim() || isSubmitting"
                    >
                        {{ isSubmitting ? 'Posting...' : 'Post Comment' }}
                    </button>
                </div>
            </form>
        </div>
        
        <div v-else class="text-center py-4 text-sm text-gray-500 border-t border-gray-200 pt-6">
            <a href="/login" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a> to add a comment
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    comments: any[];
    postId: number;
    isAuthenticated?: boolean;
    currentUserId?: number;
}>();

const emit = defineEmits<{
    'comment-added': [];
}>();

const newCommentBody = ref('');
const replyBodies = ref<Record<number, string>>({});
const showReplyForm = ref<number | null>(null);
const isSubmitting = ref(false);

const canEditComment = (comment: any): boolean => {
    return props.isAuthenticated && props.currentUserId === comment.user?.id;
};

const canDeleteComment = (comment: any): boolean => {
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
    
    try {
        await router.post('/comments', {
            post_id: props.postId,
            body: commentBody,
            is_inline: false,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                newCommentBody.value = '';
                emit('comment-added');
                router.reload({ only: ['post'] });
            },
            onError: (errors) => {
                console.error('Failed to create comment:', errors);
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

const handleReply = async (parentId: number) => {
    const replyBody = replyBodies.value[parentId];
    if (!replyBody?.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    
    try {
        await router.post('/comments', {
            post_id: props.postId,
            parent_id: parentId,
            body: replyBody,
            is_inline: false,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                replyBodies.value[parentId] = '';
                showReplyForm.value = null;
                emit('comment-added');
                router.reload({ only: ['post'] });
            },
            onError: (errors) => {
                console.error('Failed to create reply:', errors);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } catch (error) {
        console.error('Error submitting reply:', error);
        isSubmitting.value = false;
    }
};

const handleEdit = (comment: any) => {
    // Emit event to parent to handle editing
    console.log('Edit comment', comment);
};

const handleDelete = async (commentId: number) => {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
        await router.delete(`/comments/${commentId}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['post'] });
            },
            onError: (errors) => {
                console.error('Failed to delete comment:', errors);
            },
        });
    } catch (error) {
        console.error('Error deleting comment:', error);
    }
};
</script>

<style scoped>
.general-comments {
    min-height: 200px;
}
</style>

