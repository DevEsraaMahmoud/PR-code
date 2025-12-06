<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-100 mb-8">Edit Post</h1>
            <form @submit.prevent="submit" class="bg-gray-800 rounded-xl shadow-md p-8 border border-gray-700">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                        Title
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        class="w-full px-4 py-3 border border-gray-600 rounded-lg bg-gray-700 text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Enter post title..."
                    />
                    <div v-if="errors.title" class="mt-1 text-sm text-red-400">{{ errors.title }}</div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-300">Content</label>
                        <div class="space-x-2">
                            <button
                                type="button"
                                @click="addTextBlock"
                                class="px-3 py-1 text-sm bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-colors"
                            >
                                + Text
                            </button>
                            <button
                                type="button"
                                @click="addCodeBlock"
                                class="px-3 py-1 text-sm bg-blue-900/30 text-blue-300 rounded-lg hover:bg-blue-900/50 transition-colors"
                            >
                                + Code
                            </button>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <CodeBlockEditor
                            v-for="(block, index) in form.body"
                            :key="index"
                            :block="block"
                            :index="index"
                            :blocks="form.body"
                            @update="updateBlock"
                            @remove="removeBlock"
                        />
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:shadow-lg transform hover:scale-105 transition-all disabled:opacity-50"
                    >
                        {{ processing ? 'Updating...' : 'Update Post' }}
                    </button>
                    <Link :href="`/posts/${post.id}`" class="px-6 py-3 bg-gray-700 text-gray-300 font-semibold rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CodeBlockEditor from '@/Components/CodeBlockEditor.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    post: {
        id: number;
        title: string;
        body?: any[];
    };
    errors?: Record<string, string>;
}>();

// Initialize form with existing post data
const form = useForm({
    title: props.post.title || '',
    body: props.post.body && Array.isArray(props.post.body) && props.post.body.length > 0
        ? props.post.body
        : [{ type: 'text', content: '' }],
});

const processing = computed(() => form.processing);

const addTextBlock = () => {
    form.body.push({ type: 'text', content: '' });
};

const addCodeBlock = () => {
    form.body.push({ type: 'code', content: '', language: 'javascript' });
};

const updateBlock = (index: number, block: any) => {
    form.body[index] = block;
};

const removeBlock = (index: number) => {
    if (form.body.length > 1) {
        form.body.splice(index, 1);
    }
};

const submit = () => {
    form.put(`/posts/${props.post.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>

