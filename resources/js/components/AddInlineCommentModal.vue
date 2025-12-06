<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ parentComment ? 'Reply to Comment' : 'Add Comment' }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="parentComment" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-sm text-gray-600 mb-1">{{ parentComment.user.name }}</div>
                    <div class="text-sm text-gray-800">{{ parentComment.body }}</div>
                </div>

                <div v-if="startLine > 0 && endLine > 0" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-800">
                        Commenting on lines <strong>{{ startLine }}-{{ endLine }}</strong>
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Comment
                    </label>
                    <textarea
                        v-model="commentBody"
                        placeholder="Write your comment..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        rows="6"
                        required
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitComment"
                        :disabled="!commentBody.trim() || submitting"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ submitting ? 'Submitting...' : (parentComment ? 'Reply' : 'Post Comment') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    startLine?: number;
    endLine?: number;
    snippetId?: number;
    postId?: number;
    parentComment?: any;
}>();

const emit = defineEmits<{
    submit: [data: any];
    close: [];
}>();

const commentBody = ref('');
const submitting = ref(false);

watch(() => props.show, (newVal) => {
    if (!newVal) {
        commentBody.value = '';
        submitting.value = false;
    }
});

const submitComment = () => {
    if (!commentBody.value.trim()) return;

    submitting.value = true;

    const data: any = {
        body: commentBody.value,
    };

    if (props.parentComment) {
        data.parent_id = props.parentComment.id;
        data.post_id = props.postId;
        data.is_inline = false;
    } else if (props.snippetId && props.startLine && props.endLine) {
        data.snippet_id = props.snippetId;
        data.post_id = props.postId;
        data.is_inline = true;
        data.start_line = props.startLine;
        data.end_line = props.endLine;
    } else if (props.postId) {
        data.post_id = props.postId;
        data.is_inline = false;
    }

    emit('submit', data);
    
    // Reset after a short delay
    setTimeout(() => {
        submitting.value = false;
    }, 1000);
};
</script>
