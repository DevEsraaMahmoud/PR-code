<template>
    <AppLayout>
        <div>
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-100 mb-4">
                    Share Code, Get Feedback
                </h1>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
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
                        class="flex-1 px-4 py-3 border border-gray-600 rounded-lg bg-gray-800 text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        @keyup.enter="search"
                    />
                    <select
                        v-model="selectedLanguage"
                        class="px-4 py-3 border border-gray-600 rounded-lg bg-gray-800 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
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
                        class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium"
                    >
                        Search
                    </button>
                </div>
            </div>

            <!-- Posts Grid -->
            <div v-if="!posts || posts.length === 0" class="text-center py-16">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-gray-500 text-lg">No posts found. Be the first to create one!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <PostCard
                    v-for="post in normalizedPosts"
                    :key="post.id"
                    :post="post"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    posts?: any[] | null;
}>();

const posts = computed(() => props.posts || []);

// Normalize posts to match PostCard component expected format
const normalizedPosts = computed(() => {
    return posts.value.map((post: any) => {
        // Extract code from snippets if available
        const code = post.snippets && post.snippets.length > 0 
            ? {
                language: post.snippets[0].language || 'text',
                content: post.snippets[0].content || ''
            }
            : null;

        // Extract body HTML from body blocks if available
        const bodyHtml = Array.isArray(post.body) 
            ? post.body
                .filter((block: any) => block.type === 'text')
                .map((block: any) => block.content)
                .join('\n\n')
            : (post.body_html || post.body || '');

        return {
            id: post.id,
            author: {
                id: post.user?.id || post.author?.id,
                name: post.user?.name || post.author?.name || 'Unknown',
                avatar_url: post.user?.avatar_url || post.author?.avatar_url,
                handle: post.user?.handle || post.author?.handle,
            },
            title: post.title,
            body: bodyHtml,
            body_html: bodyHtml,
            code: code,
            stats: {
                likes: post.likes_count || post.stats?.likes || 0,
                comments: post.comments_count || post.stats?.comments || 0,
                views: post.views || post.stats?.views || 0,
            },
            created_at: post.created_at,
        };
    });
});

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
</script>

