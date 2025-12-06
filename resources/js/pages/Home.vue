<template>
    <AppLayout>
        <div>
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Share Code, Get Feedback
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    A platform for developers to share code snippets and receive inline comments, just like GitHub PR reviews.
                </p>
                <Link v-if="$page.props.auth.user" href="/posts/create" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Post
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="mb-8">
                <div class="flex flex-col md:flex-row gap-4 max-w-3xl mx-auto">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search posts by title..."
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        @keyup.enter="search"
                    />
                    <select
                        v-model="selectedLanguage"
                        class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        @change="search"
                    >
                        <option value="">All Languages</option>
                        <option value="javascript">JavaScript</option>
                        <option value="typescript">TypeScript</option>
                        <option value="python">Python</option>
                        <option value="php">PHP</option>
                        <option value="java">Java</option>
                        <option value="go">Go</option>
                        <option value="rust">Rust</option>
                    </select>
                    <button
                        @click="search"
                        class="px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors font-medium"
                    >
                        Search
                    </button>
                </div>
            </div>

            <!-- Posts Grid -->
            <div v-if="posts.length === 0" class="text-center py-16">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-gray-500 text-lg">No posts found. Be the first to create one!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="post in posts"
                    :key="post.id"
                    class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 cursor-pointer transform hover:-translate-y-1"
                    @click="$inertia.visit(`/posts/${post.id}`)"
                >
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                            {{ post.title }}
                        </h3>
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <span>{{ post.user.name }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ formatDate(post.created_at) }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="snippet in post.snippets"
                                :key="snippet.id"
                                class="px-2 py-1 bg-gradient-to-r from-blue-50 to-purple-50 text-blue-700 text-xs font-medium rounded-md border border-blue-100"
                            >
                                {{ snippet.language }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    posts: any[];
}>();

const searchQuery = ref('');
const selectedLanguage = ref('');

const search = () => {
    router.get('/', {
        q: searchQuery.value || undefined,
        language: selectedLanguage.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

