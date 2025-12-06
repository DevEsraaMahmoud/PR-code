<template>
  <AppLayout>
    <div class="create-post-page">
      <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          href="/feed"
          class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-100 mb-4 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Cancel
        </Link>
        <h1 class="text-3xl font-bold text-gray-100">Create New Post</h1>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitPost" class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <!-- Title -->
        <div class="mb-6">
          <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
            Title *
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Give your post a title..."
          />
          <p v-if="errors.title" class="mt-1 text-sm text-red-400">{{ errors.title }}</p>
        </div>

        <!-- Rich Text Body -->
        <div class="mb-6">
          <label for="body" class="block text-sm font-medium text-gray-300 mb-2">
            Description *
          </label>
          <textarea
            id="body"
            v-model="form.body"
            required
            rows="6"
            class="w-full px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            placeholder="Describe your code, what problem it solves, or what you learned..."
          ></textarea>
          <p class="mt-1 text-xs text-gray-400">Markdown is supported</p>
          <p v-if="errors.body" class="mt-1 text-sm text-red-400">{{ errors.body }}</p>
        </div>

        <!-- Code Block -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <label for="code-language" class="block text-sm font-medium text-gray-300">
              Code Language
            </label>
            <button
              type="button"
              @click="showCodeEditor = !showCodeEditor"
              class="text-sm text-blue-400 hover:underline"
            >
              {{ showCodeEditor ? 'Hide Code Editor' : 'Show Code Editor' }}
            </button>
          </div>

          <div v-if="showCodeEditor" class="space-y-4">
            <select
              id="code-language"
              v-model="form.code_language"
              class="w-full px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select language...</option>
              <option value="php">PHP</option>
              <option value="javascript">JavaScript</option>
              <option value="typescript">TypeScript</option>
              <option value="python">Python</option>
              <option value="java">Java</option>
              <option value="bash">Bash</option>
              <option value="sql">SQL</option>
              <option value="json">JSON</option>
              <option value="markdown">Markdown</option>
              <option value="css">CSS</option>
              <option value="scss">SCSS</option>
            </select>

            <div>
              <label for="code-content" class="block text-sm font-medium text-gray-300 mb-2">
                Code Content
              </label>
              <textarea
                id="code-content"
                v-model="form.code_content"
                rows="12"
                class="w-full px-4 py-2 font-mono text-sm border border-gray-600 rounded-md bg-gray-900 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                placeholder="Paste your code here..."
              ></textarea>
              <p class="mt-1 text-xs text-gray-400">Tip: For a better editing experience, consider using CodeMirror or Monaco Editor</p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-700">
          <Link
            href="/feed"
            class="px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-md transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
          >
            {{ processing ? 'Publishing...' : 'Publish Post' }}
          </button>
        </div>
      </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/**
 * Props expected from Laravel controller:
 * 
 * @prop {Object} errors - Validation errors (optional)
 */
const props = defineProps<{
  errors?: Record<string, string>;
}>();

const showCodeEditor = ref(false);

const form = useForm({
  title: '',
  body: '',
  code_language: '',
  code_content: '',
});

const processing = ref(false);

function submitPost() {
  processing.value = true;
  
  form.post('/posts', {
    preserveScroll: true,
    onSuccess: () => {
      // Redirect handled by Laravel
    },
    onError: () => {
      processing.value = false;
    },
    onFinish: () => {
      processing.value = false;
    },
  });
}
</script>

<style scoped>
.create-post-page {
  min-height: 100vh;
  background-color: #111827;
}
</style>

