<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Three Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Sidebar -->
                <aside class="lg:col-span-3 space-y-4">
                    <!-- User Profile Card -->
                    <div v-if="$page.props.auth.user" class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                        <div class="p-4 pb-6">
                            <div class="flex flex-col items-center text-center mb-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-3">
                                    {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                                </div>
                                <h3 class="font-semibold text-gray-100 mb-1">{{ $page.props.auth.user.name }}</h3>
                                <p class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-700 px-4 py-3">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-400">Profile views</span>
                                <span class="text-gray-300 font-medium">0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Post impressions</span>
                                <span class="text-gray-300 font-medium">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                        <h4 class="text-sm font-semibold text-gray-300 mb-3">Quick Links</h4>
                        <div class="space-y-2">
                            <Link href="/posts/create" class="flex items-center gap-3 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Post
                            </Link>
                            <Link href="/feed" class="flex items-center gap-3 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                My Feed
                            </Link>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                        <h4 class="text-sm font-semibold text-gray-300 mb-3">Search</h4>
                        <div class="space-y-2">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search posts..."
                                    class="w-full pl-9 pr-3 py-2 border border-gray-600 rounded-lg bg-gray-900 text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm"
                                    @keyup.enter="search"
                                />
                            </div>
                            <select
                                v-model="selectedLanguage"
                                class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm"
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
                        </div>
                    </div>
                </aside>

                <!-- Center Feed -->
                <main class="lg:col-span-6 space-y-4">
                    <!-- Start a Post Section -->
                    <div v-if="$page.props.auth.user" class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <Link 
                                href="/posts/create" 
                                class="flex-1 px-4 py-2 bg-gray-900 border border-gray-600 rounded-lg text-gray-400 hover:border-gray-500 hover:text-gray-300 transition-colors text-sm"
                            >
                                Start a post...
                            </Link>
                        </div>
                        <div class="flex items-center justify-around pt-3 border-t border-gray-700">
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Photo
                            </Link>
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Code
                            </Link>
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-gray-400 hover:text-blue-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Write article
                            </Link>
                        </div>
                    </div>

                    <!-- Feed Header -->
                    <div v-if="posts && posts.length > 0" class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-300">Suggested</span>
                        </div>
                        <select class="text-sm text-gray-400 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option>Sort by: Top</option>
                            <option>Sort by: Recent</option>
                        </select>
                    </div>

                    <!-- Posts Feed -->
                    <div v-if="!posts || posts.length === 0" class="text-center py-16">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg mb-2">No posts yet</p>
                        <p class="text-gray-400 text-sm mb-4">Be the first to share your code!</p>
                        <Link 
                            v-if="$page.props.auth.user"
                            href="/posts/create" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all"
                        >
                            Create Your First Post
                        </Link>
                    </div>

                    <div v-else class="space-y-4">
                        <PostCard
                            v-for="post in normalizedPosts"
                            :key="post.id"
                            :post="post"
                        />
                    </div>
                </main>

                <!-- Right Sidebar -->
                <aside class="lg:col-span-3 space-y-4">
                    <!-- Trending -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                        <h4 class="text-sm font-semibold text-gray-300 mb-3">Trending</h4>
                        <div class="space-y-3">
                            <div class="text-sm">
                                <p class="text-gray-400 mb-1">#laravel</p>
                                <p class="text-xs text-gray-500">12 posts</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-400 mb-1">#vuejs</p>
                                <p class="text-xs text-gray-500">8 posts</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-400 mb-1">#javascript</p>
                                <p class="text-xs text-gray-500">15 posts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Suggested to Follow -->
                    <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                        <h4 class="text-sm font-semibold text-gray-300 mb-3">Add to your feed</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                        D
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-200">Developer</p>
                                        <p class="text-xs text-gray-400">Software Development</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-400 hover:text-blue-300 font-medium px-3 py-1 border border-gray-600 rounded-full hover:border-blue-500 transition-colors">
                                    Follow
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                        C
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-200">CodeHub</p>
                                        <p class="text-xs text-gray-400">Technology</p>
                                    </div>
                                </div>
                                <button class="text-xs text-blue-400 hover:text-blue-300 font-medium px-3 py-1 border border-gray-600 rounded-full hover:border-blue-500 transition-colors">
                                    Follow
                                </button>
                            </div>
                        </div>
                        <Link href="#" class="block text-xs text-gray-400 hover:text-blue-400 mt-3 pt-3 border-t border-gray-700">
                            View all recommendations →
                        </Link>
                    </div>

                    <!-- Footer Links -->
                    <div class="text-xs text-gray-500 space-y-1">
                        <div class="flex flex-wrap gap-2">
                            <a href="#" class="hover:text-blue-400 transition-colors">About</a>
                            <span>·</span>
                            <a href="#" class="hover:text-blue-400 transition-colors">Accessibility</a>
                            <span>·</span>
                            <a href="#" class="hover:text-blue-400 transition-colors">Help</a>
                        </div>
                        <p class="text-gray-600 mt-2">© 2025 PR Code</p>
                    </div>
                </aside>
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

