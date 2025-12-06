<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">PR Code</h1>
            <div class="flex space-x-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by title..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    @keyup.enter="handleSearch"
                />
                <select
                    v-model="selectedLanguage"
                    class="px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    @change="handleSearch"
                >
                    <option value="">All Languages</option>
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="php">PHP</option>
                    <option value="java">Java</option>
                    <option value="typescript">TypeScript</option>
                    <option value="go">Go</option>
                    <option value="rust">Rust</option>
                </select>
                <button
                    @click="handleSearch"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Search
                </button>
            </div>
        </div>

        <div v-if="postsStore.loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="postsStore.posts.length === 0" class="text-center py-12">
            <p class="text-gray-500">No posts found.</p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="post in postsStore.posts"
                :key="post.id"
                class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer"
                @click="$router.push(`/posts/${post.id}`)"
            >
                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ post.title }}</h2>
                <p class="text-sm text-gray-500 mb-4">
                    By {{ post.user.name }} • {{ formatDate(post.created_at) }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="snippet in post.snippets"
                        :key="snippet.id"
                        class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                    >
                        {{ snippet.language }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePostsStore } from '../stores/posts';

const postsStore = usePostsStore();
const searchQuery = ref('');
const selectedLanguage = ref('');

const handleSearch = () => {
    if (selectedLanguage.value) {
        postsStore.fetchPosts('', selectedLanguage.value);
    } else if (searchQuery.value) {
        postsStore.fetchPosts(searchQuery.value);
    } else {
        postsStore.fetchPosts();
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

onMounted(() => {
    postsStore.fetchPosts();
});
</script>

