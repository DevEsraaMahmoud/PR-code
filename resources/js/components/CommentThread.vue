<template>
    <div class="space-y-4">
        <div
            v-for="comment in comments"
            :key="comment.id"
            class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow"
        >
            <!-- Comment Header -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                        {{ comment.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ comment.user.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</p>
                    </div>
                </div>
                <div v-if="comment.is_inline" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                    Lines {{ comment.start_line }}-{{ comment.end_line }}
                </div>
            </div>

            <!-- Comment Body -->
            <div class="text-gray-700 text-sm mb-3 whitespace-pre-wrap">{{ comment.body }}</div>

            <!-- Comment Actions -->
            <div class="flex items-center space-x-4 text-sm">
                <button
                    v-if="isAuthenticated"
                    @click="toggleReply(comment.id)"
                    class="text-blue-600 hover:text-blue-700 font-medium"
                >
                    {{ showReplyForm === comment.id ? 'Cancel' : 'Reply' }}
                </button>
                <button
                    v-if="isAuthenticated && canEdit(comment)"
                    @click="editComment(comment)"
                    class="text-gray-600 hover:text-gray-700"
                >
                    Edit
                </button>
                <button
                    v-if="isAuthenticated && canDelete(comment)"
                    @click="deleteComment(comment.id)"
                    class="text-red-600 hover:text-red-700"
                >
                    Delete
                </button>
                <div class="flex items-center space-x-1 text-gray-500">
                    <button
                        v-if="isAuthenticated"
                        @click="toggleLike(comment.id)"
                        class="hover:text-blue-600 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="isLiked(comment) ? 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' : 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'"
                            />
                        </svg>
                    </button>
                    <span class="text-xs">{{ comment.likes_count || 0 }}</span>
                </div>
            </div>

            <!-- Reply Form -->
            <div v-if="showReplyForm === comment.id" class="mt-4 pt-4 border-t border-gray-200">
                <textarea
                    v-model="replyText"
                    placeholder="Write a reply..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    rows="3"
                ></textarea>
                <div class="flex justify-end space-x-2 mt-2">
                    <button
                        @click="cancelReply"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitReply(comment.id)"
                        :disabled="!replyText.trim()"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Reply
                    </button>
                </div>
            </div>

            <!-- Nested Replies -->
            <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 ml-8 space-y-3 border-l-2 border-gray-200 pl-4">
                <CommentThread
                    :comments="comment.replies"
                    :is-authenticated="isAuthenticated"
                    :current-user-id="currentUserId"
                    @reply="handleReply"
                    @edit="handleEdit"
                    @delete="handleDelete"
                    @like="handleLike"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    comments: any[];
    isAuthenticated?: boolean;
    currentUserId?: number;
}>();

const emit = defineEmits<{
    reply: [commentId: number, body: string];
    edit: [comment: any];
    delete: [commentId: number];
    like: [commentId: number];
}>();

const showReplyForm = ref<number | null>(null);
const replyText = ref('');

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
};

const toggleReply = (commentId: number) => {
    if (showReplyForm.value === commentId) {
        showReplyForm.value = null;
        replyText.value = '';
    } else {
        showReplyForm.value = commentId;
    }
};

const cancelReply = () => {
    showReplyForm.value = null;
    replyText.value = '';
};

const submitReply = (parentId: number) => {
    if (!replyText.value.trim()) return;
    emit('reply', parentId, replyText.value);
    replyText.value = '';
    showReplyForm.value = null;
};

const editComment = (comment: any) => {
    emit('edit', comment);
};

const deleteComment = (commentId: number) => {
    if (confirm('Are you sure you want to delete this comment?')) {
        emit('delete', commentId);
    }
};

const toggleLike = (commentId: number) => {
    emit('like', commentId);
};

const canEdit = (comment: any) => {
    return props.currentUserId && comment.user_id === props.currentUserId;
};

const canDelete = (comment: any) => {
    return props.currentUserId && comment.user_id === props.currentUserId;
};

const isLiked = (comment: any) => {
    if (!props.currentUserId || !comment.likes) return false;
    return comment.likes.some((like: any) => like.user_id === props.currentUserId);
};

const handleReply = (commentId: number, body: string) => {
    emit('reply', commentId, body);
};

const handleEdit = (comment: any) => {
    emit('edit', comment);
};

const handleDelete = (commentId: number) => {
    emit('delete', commentId);
};

const handleLike = (commentId: number) => {
    emit('like', commentId);
};
</script>

