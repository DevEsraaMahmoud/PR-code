<template>
    <AppLayout>
        <template #sidebar>
            <!-- VS Code Style Sidebar Content -->
            <div class="p-4 space-y-4">
                <!-- User Profile Card -->
                <div v-if="$page.props.auth.user" class="mb-4">
                    <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Profile</h3>
                    <div class="px-2 py-3 bg-[#2a2d2e] rounded border border-[#3e3e42]">
                        <div class="flex flex-col items-center text-center mb-3">
                            <div class="w-16 h-16 bg-[#007acc] rounded-full flex items-center justify-center text-white text-xl font-bold mb-2">
                                {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <h3 class="font-semibold text-[#cccccc] mb-1 text-sm">{{ $page.props.auth.user.name }}</h3>
                            <p class="text-xs text-[#858585]">{{ $page.props.auth.user.email }}</p>
                        </div>
                        <div class="border-t border-[#3e3e42] pt-2 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-[#858585]">Profile views</span>
                                <span class="text-[#cccccc] font-medium">0</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-[#858585]">Post impressions</span>
                                <span class="text-[#cccccc] font-medium">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Quick Links</h3>
                    <div class="space-y-1">
                        <Link href="/posts/create" class="sidebar-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Create Post</span>
                        </Link>
                        <Link href="/feed" class="sidebar-item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span>My Feed</span>
                        </Link>
                    </div>
                </div>

                <!-- Search -->
                <div>
                    <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Search</h3>
                    <div class="space-y-2 px-2">
                        <div class="relative">
                            <svg class="absolute left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-[#858585]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search posts..."
                                class="w-full pl-8 pr-2 py-1.5 border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] text-xs"
                                @keyup.enter="search"
                            />
                        </div>
                        <select
                            v-model="selectedLanguage"
                            class="w-full px-2 py-1.5 border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] text-xs"
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

                <!-- Trending -->
                <div>
                    <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Trending</h3>
                    <div class="space-y-2 px-2">
                        <div class="text-xs">
                            <p class="text-[#cccccc] mb-0.5">#laravel</p>
                            <p class="text-[#858585]">12 posts</p>
                        </div>
                        <div class="text-xs">
                            <p class="text-[#cccccc] mb-0.5">#vuejs</p>
                            <p class="text-[#858585]">8 posts</p>
                        </div>
                        <div class="text-xs">
                            <p class="text-[#cccccc] mb-0.5">#javascript</p>
                            <p class="text-[#858585]">15 posts</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="p-6">
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Main Content Area -->
                <main class="lg:col-span-8 space-y-6">
                    <!-- Start a Post Section -->
                    <div v-if="$page.props.auth.user" class="bg-[#252526] rounded border border-[#3e3e42] p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-[#007acc] rounded-full flex items-center justify-center text-white font-semibold">
                                {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <Link 
                                href="/posts/create" 
                                class="flex-1 px-4 py-2 bg-[#1e1e1e] border border-[#3e3e42] rounded text-[#858585] hover:border-[#007acc] hover:text-[#cccccc] transition-colors text-sm"
                            >
                                Start a post...
                            </Link>
                        </div>
                        <div class="flex items-center justify-around pt-3 border-t border-[#3e3e42]">
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-[#858585] hover:text-[#007acc] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Photo
                            </Link>
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-[#858585] hover:text-[#007acc] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Code
                            </Link>
                            <Link href="/posts/create" class="flex items-center gap-2 text-sm text-[#858585] hover:text-[#007acc] transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Write article
                            </Link>
                        </div>
                    </div>

                    <!-- Feed Header with Sorting -->
                    <div v-if="posts && posts.length > 0" class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-[#cccccc]">Suggested</span>
                        </div>
                        <div class="flex items-center gap-1 bg-[#252526] border border-[#3e3e42] rounded-lg p-1">
                            <button
                                @click="sortBy = 'recent'"
                                :class="['px-3 py-1 text-xs rounded transition-colors', sortBy === 'recent' ? 'bg-[#007acc] text-white' : 'text-[#858585] hover:text-[#cccccc]']"
                            >
                                Recent
                            </button>
                            <button
                                @click="sortBy = 'top'"
                                :class="['px-3 py-1 text-xs rounded transition-colors', sortBy === 'top' ? 'bg-[#007acc] text-white' : 'text-[#858585] hover:text-[#cccccc]']"
                            >
                                Top
                            </button>
                            <button
                                @click="sortBy = 'trending'"
                                :class="['px-3 py-1 text-xs rounded transition-colors', sortBy === 'trending' ? 'bg-[#007acc] text-white' : 'text-[#858585] hover:text-[#cccccc]']"
                            >
                                Trending
                            </button>
                        </div>
                    </div>

                    <!-- Posts Feed -->
                    <div v-if="!posts || posts.length === 0" class="text-center py-16">
                        <div class="text-[#858585] mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-[#858585] text-lg mb-2">No posts yet</p>
                        <p class="text-[#858585] text-sm mb-4">Be the first to share your code!</p>
                        <Link 
                            v-if="$page.props.auth.user"
                            href="/posts/create" 
                            class="inline-flex items-center px-4 py-2 bg-[#007acc] text-white font-semibold rounded hover:bg-[#005a9e] transition-all"
                        >
                            Create Your First Post
                        </Link>
                    </div>

                    <div v-else class="space-y-6">
                        <PostCard
                            v-for="post in normalizedPosts"
                            :key="post.id"
                            :post="post"
                        />
                    </div>
                </main>

                <!-- Right Sidebar -->
                <aside class="lg:col-span-4 space-y-4">
                    <!-- Suggested to Follow -->
                    <div class="bg-[#252526] rounded border border-[#3e3e42] p-4">
                        <h4 class="text-sm font-semibold text-[#cccccc] mb-3">Add to your feed</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-[#007acc] rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                        D
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#cccccc]">Developer</p>
                                        <p class="text-xs text-[#858585]">Software Development</p>
                                    </div>
                                </div>
                                <button class="text-xs text-[#007acc] hover:text-[#4fc3f7] font-medium px-3 py-1 border border-[#3e3e42] rounded-full hover:border-[#007acc] transition-colors">
                                    Follow
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 bg-[#007acc] rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                        C
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#cccccc]">CodeHub</p>
                                        <p class="text-xs text-[#858585]">Technology</p>
                                    </div>
                                </div>
                                <button class="text-xs text-[#007acc] hover:text-[#4fc3f7] font-medium px-3 py-1 border border-[#3e3e42] rounded-full hover:border-[#007acc] transition-colors">
                                    Follow
                                </button>
                            </div>
                        </div>
                        <Link href="#" class="block text-xs text-[#858585] hover:text-[#007acc] mt-3 pt-3 border-t border-[#3e3e42]">
                            View all recommendations →
                        </Link>
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
    // Sort posts based on selected sort option
    let sortedPosts = [...posts.value];
    
    if (sortBy.value === 'recent') {
        sortedPosts.sort((a: any, b: any) => {
            const dateA = new Date(a.created_at || 0).getTime();
            const dateB = new Date(b.created_at || 0).getTime();
            return dateB - dateA;
        });
    } else if (sortBy.value === 'top') {
        sortedPosts.sort((a: any, b: any) => {
            const likesA = a.likes_count || a.stats?.likes || 0;
            const likesB = b.likes_count || b.stats?.likes || 0;
            return likesB - likesA;
        });
    } else if (sortBy.value === 'trending') {
        sortedPosts.sort((a: any, b: any) => {
            // Trending = combination of recent + engagement
            const dateA = new Date(a.created_at || 0).getTime();
            const dateB = new Date(b.created_at || 0).getTime();
            const daysDiffA = (Date.now() - dateA) / (1000 * 60 * 60 * 24);
            const daysDiffB = (Date.now() - dateB) / (1000 * 60 * 60 * 24);
            
            const engagementA = (a.likes_count || 0) + (a.comments_count || 0) * 2;
            const engagementB = (b.likes_count || 0) + (b.comments_count || 0) * 2;
            
            // Weight: more recent + more engagement = trending
            const scoreA = engagementA / Math.max(daysDiffA, 0.1);
            const scoreB = engagementB / Math.max(daysDiffB, 0.1);
            
            return scoreB - scoreA;
        });
    }
    
    return sortedPosts.map((post: any) => {
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
            // Preserve original body array if it exists, otherwise use bodyHtml
            body: Array.isArray(post.body) ? post.body : bodyHtml,
            body_html: bodyHtml,
            blocks: post.blocks, // Preserve blocks if they exist
            snippets: post.snippets, // Preserve snippets for tab opening
            code: code,
            inlineIndex: post.inlineIndex || post.inline_comments_index, // Preserve inline comments index
            stats: {
                likes: post.likes_count || post.stats?.likes || 0,
                comments: post.comments_count || post.stats?.comments || 0,
                views: post.views || post.stats?.views || 0,
            },
            created_at: post.created_at,
            // Preserve user object for PostTabContent
            user: post.user || post.author,
            likes_count: post.likes_count || post.stats?.likes || 0,
            comments_count: post.comments_count || post.stats?.comments || 0,
        };
    });
});

const searchQuery = ref('');
const selectedLanguage = ref('');
const sortBy = ref<'recent' | 'top' | 'trending'>('recent');

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

<style scoped>
.sidebar-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    color: #cccccc;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.sidebar-item:hover {
    background-color: #2a2d2e;
}
</style>

