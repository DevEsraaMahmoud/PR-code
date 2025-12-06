<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Post</h1>
        <form @submit.prevent="handleSubmit">
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Title
                </label>
                <input
                    id="title"
                    v-model="title"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter post title..."
                />
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Content</label>
                    <button
                        type="button"
                        @click="addTextBlock"
                        class="text-sm text-blue-600 hover:text-blue-700"
                    >
                        + Add Text
                    </button>
                    <button
                        type="button"
                        @click="addCodeBlock"
                        class="text-sm text-blue-600 hover:text-blue-700"
                    >
                        + Add Code
                    </button>
                </div>
                <div class="space-y-4">
                    <CodeBlockEditor
                        v-for="(block, index) in blocks"
                        :key="index"
                        :block="block"
                        :index="index"
                        :blocks="blocks"
                        @update="updateBlock"
                        @remove="removeBlock"
                    />
                </div>
            </div>

            <div v-if="error" class="mb-4 text-red-600 text-sm">{{ error }}</div>
            <div class="flex space-x-4">
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ loading ? 'Creating...' : 'Create Post' }}
                </button>
                <router-link
                    to="/"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                    Cancel
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePostsStore } from '../stores/posts';
import CodeBlockEditor from '../components/CodeBlockEditor.vue';

const router = useRouter();
const postsStore = usePostsStore();

const title = ref('');
const blocks = ref<Array<{ type: string; content: string; language?: string }>>([
    { type: 'text', content: '' },
]);
const loading = ref(false);
const error = ref('');

const addTextBlock = () => {
    blocks.value.push({ type: 'text', content: '' });
};

const addCodeBlock = () => {
    blocks.value.push({ type: 'code', content: '', language: 'javascript' });
};

const updateBlock = (index: number, block: any) => {
    blocks.value[index] = block;
};

const removeBlock = (index: number) => {
    if (blocks.value.length > 1) {
        blocks.value.splice(index, 1);
    }
};

const handleSubmit = async () => {
    loading.value = true;
    error.value = '';
    try {
        const post = await postsStore.createPost({
            title: title.value,
            body: blocks.value,
        });
        router.push(`/posts/${post.id}`);
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to create post';
    } finally {
        loading.value = false;
    }
};
</script>

