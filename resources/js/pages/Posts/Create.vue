<template>
    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-[#cccccc] mb-6">Create New Post</h1>
            <form @submit.prevent="submit" class="bg-[#252526] rounded border border-[#3e3e42] p-6">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-[#cccccc] mb-2">
                        Title
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        class="w-full px-4 py-2 border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] transition-all"
                        placeholder="Enter post title..."
                    />
                    <div v-if="errors?.title" class="mt-1 text-sm text-red-400">{{ errors.title }}</div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-[#cccccc]">Content</label>
                        <div class="space-x-2">
                            <button
                                type="button"
                                @click="addTextBlock"
                                class="px-3 py-1.5 text-sm bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e] transition-colors"
                            >
                                + Text
                            </button>
                            <button
                                type="button"
                                @click="addCodeBlock"
                                class="px-3 py-1.5 text-sm bg-[#094771] text-[#4fc3f7] rounded hover:bg-[#005a9e] transition-colors"
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
                        class="px-6 py-2 bg-[#007acc] text-white font-semibold rounded hover:bg-[#005a9e] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ processing ? 'Creating...' : 'Create Post' }}
                    </button>
                    <Link href="/" class="px-6 py-2 bg-[#3e3e42] text-[#cccccc] font-semibold rounded hover:bg-[#4e4e4e] transition-colors">
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
import CodeBlockEditor from '@/components/CodeBlockEditor.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    errors?: Record<string, string>;
}>();

const form = useForm({
    title: '',
    body: [{ type: 'text', content: '' }],
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
    form.post('/posts', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};
</script>


