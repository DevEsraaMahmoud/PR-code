<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="close"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Add Comment
                    <span v-if="startLine && endLine" class="text-sm text-gray-500">
                        (Lines {{ startLine }}-{{ endLine }})
                    </span>
                </h3>
                <div v-if="parentComment" class="mb-4 p-3 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600">
                        Replying to: <span class="font-medium">{{ parentComment.user.name }}</span>
                    </p>
                    <p class="text-sm text-gray-700 mt-1">{{ parentComment.body }}</p>
                </div>
                <textarea
                    v-model="commentBody"
                    rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter your comment..."
                ></textarea>
                <div v-if="error" class="mt-2 text-red-600 text-sm">{{ error }}</div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button
                        @click="close"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitComment"
                        :disabled="!commentBody.trim() || loading"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ loading ? 'Submitting...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { defineProps, defineEmits } from 'vue';

const props = defineProps<{
    show: boolean;
    startLine?: number;
    endLine?: number;
    snippetId: number;
    parentComment?: any;
}>();

const emit = defineEmits<{
    submit: [data: { snippet_id: number; start_line: number; end_line: number; body: string; parent_id?: number }];
    close: [];
}>();

const commentBody = ref('');
const loading = ref(false);
const error = ref('');

watch(() => props.show, (newVal) => {
    if (!newVal) {
        commentBody.value = '';
        error.value = '';
    }
});

const submitComment = () => {
    if (!props.startLine || !props.endLine || !commentBody.value.trim()) {
        error.value = 'Please enter a comment';
        return;
    }

    loading.value = true;
    error.value = '';

    emit('submit', {
        snippet_id: props.snippetId,
        start_line: props.startLine,
        end_line: props.endLine,
        body: commentBody.value,
        parent_id: props.parentComment?.id,
    });

    commentBody.value = '';
    loading.value = false;
};

const close = () => {
    emit('close');
};
</script>

