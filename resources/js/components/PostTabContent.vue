<template>
    <div class="h-full overflow-y-auto p-6">
        <div v-if="loading" class="flex items-center justify-center h-full">
            <div class="text-[#858585]">Loading post...</div>
        </div>
        <div v-else-if="error" class="flex items-center justify-center h-full">
            <div class="text-red-400">Error loading post: {{ error }}</div>
        </div>
        <div v-else-if="post" class="max-w-4xl mx-auto">
            <!-- Post Header -->
            <div class="bg-[#252526] rounded border border-[#3e3e42] p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-[#cccccc] mb-4">{{ post.title }}</h1>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-[#858585]">
                                <div class="w-10 h-10 bg-[#007acc] rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    {{ post.user?.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div>
                                    <p class="font-medium text-[#cccccc]">{{ post.user?.name || 'Unknown User' }}</p>
                                    <p class="text-sm text-[#858585]">{{ formatDate(post.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="$emit('close')"
                            class="px-3 py-1.5 text-sm bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e] transition-colors"
                        >
                            Close Tab
                        </button>
                        <Link
                            :href="`/posts/${post.id}`"
                            class="px-3 py-1.5 text-sm bg-[#007acc] text-white rounded hover:bg-[#005a9e] transition-colors"
                        >
                            Open Full Page
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Post Content -->
            <div class="bg-[#252526] rounded border border-[#3e3e42] p-6 mb-6">
                <div v-if="postBlocks && postBlocks.length > 0" class="space-y-4">
                    <template v-for="(block, index) in postBlocks" :key="`block-${block.id || index}`">
                        <!-- Text Block -->
                        <div
                            v-if="block.type === 'text' && block.content"
                            class="prose prose-lg prose-invert max-w-none text-[#cccccc] whitespace-pre-wrap leading-relaxed"
                        >
                            {{ block.content }}
                        </div>
                        
                        <!-- Code Block -->
                        <div
                            v-else-if="block.type === 'code' && block.content"
                            class="my-4"
                        >
                            <CodeSnippetCompact
                                :code="block.content || ''"
                                :language="block.language || 'text'"
                                :block-id="block.id"
                                :post-id="post.id"
                                :inline-comments-index="getInlineCommentsIndex(block.id)"
                                :is-feed="false"
                            />
                        </div>
                    </template>
                </div>
                <!-- Fallback: Show code if available but no blocks -->
                <div v-else-if="hasCodeButNoBlocks" class="space-y-4">
                    <div v-if="post.body || post.body_html" class="prose prose-lg prose-invert max-w-none text-[#cccccc] whitespace-pre-wrap">
                        {{ post.body || post.body_html }}
                    </div>
                    <div v-if="codeContent" class="-mx-6">
                        <CodeSnippetCompact
                            :code="codeContent"
                            :language="codeLanguage"
                            :block-id="codeBlockId"
                            :post-id="post.id"
                            :inline-comments-index="getInlineCommentsIndex(codeBlockId)"
                            :is-feed="false"
                        />
                    </div>
                </div>
                <div v-else-if="post.body || post.body_html" class="prose prose-lg prose-invert max-w-none text-[#cccccc] whitespace-pre-wrap">
                    {{ post.body || post.body_html }}
                </div>
                <div v-else class="text-[#858585] text-center py-8">
                    No content available
                </div>
            </div>

            <!-- Post Actions -->
            <div class="bg-[#252526] rounded border border-[#3e3e42] p-4">
                <div class="flex items-center gap-4">
                    <button class="flex items-center gap-2 text-sm text-[#858585] hover:text-[#007acc] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span>{{ post.likes_count || 0 }}</span>
                    </button>
                    <button class="flex items-center gap-2 text-sm text-[#858585] hover:text-[#007acc] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>{{ post.comments_count || 0 }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import CodeSnippetCompact from '@/Components/CodeSnippetCompact.vue';
import type { Tab } from '@/composables/useTabs';

const props = defineProps<{
    tab: Tab;
}>();

const emit = defineEmits<{
    close: [];
}>();

const post = ref<any>(props.tab.postData || null);
const loading = ref(!props.tab.postData);
const error = ref<string | null>(null);

// Post blocks
const postBlocks = computed(() => {
    if (!post.value) return [];
    
    // Check if post.body is an array of blocks
    if (Array.isArray(post.value.body) && post.value.body.length > 0) {
        return post.value.body.map((block: any, index: number) => {
            // Try to match with snippet ID if it's a code block
            let blockId = block.id;
            let blockContent = block.content || '';
            let blockLanguage = block.language || 'text';
            
            if (block.type === 'code' && post.value.snippets) {
                const snippet = post.value.snippets.find((s: any) => s.block_index === index);
                if (snippet) {
                    blockId = snippet.id;
                    blockContent = snippet.code_text || snippet.content || blockContent;
                    blockLanguage = snippet.language || blockLanguage;
                }
            }
            
            return {
                id: blockId || `${block.type}-${index + 1}`,
                type: block.type,
                content: blockContent,
                language: blockLanguage,
            };
        });
    }
    
    // Check if post.blocks exists (new format)
    if (Array.isArray(post.value.blocks) && post.value.blocks.length > 0) {
        return post.value.blocks.map((block: any, index: number) => {
            // Try to match with snippet ID if it's a code block
            let blockId = block.id;
            let blockContent = block.content || '';
            let blockLanguage = block.language || 'text';
            
            if (block.type === 'code' && post.value.snippets) {
                const snippet = post.value.snippets.find((s: any) => s.block_index === index);
                if (snippet) {
                    blockId = snippet.id;
                    blockContent = snippet.code_text || snippet.content || blockContent;
                    blockLanguage = snippet.language || blockLanguage;
                }
            }
            
            return {
                ...block,
                id: blockId || block.id || `${block.type}-${index + 1}`,
                content: blockContent,
                language: blockLanguage,
            };
        });
    }
    
    // Fallback: create blocks from old format
    const blocks: any[] = [];
    
    // Add text block
    if (post.value.body || post.value.body_html) {
        if (typeof post.value.body === 'string' || typeof post.value.body_html === 'string') {
            blocks.push({
                id: `text-${post.value.id}`,
                type: 'text',
                content: post.value.body || post.value.body_html || '',
            });
        }
    }
    
    // Add code block from snippets or code property
    if (post.value.snippets && Array.isArray(post.value.snippets) && post.value.snippets.length > 0) {
        post.value.snippets.forEach((snippet: any, index: number) => {
            blocks.push({
                id: snippet.id || `code-${index + 1}`,
                type: 'code',
                content: snippet.code_text || snippet.content || '',
                language: snippet.language || 'text',
            });
        });
    } else if (post.value.code?.content) {
        blocks.push({
            id: `code-${post.value.id}`,
            type: 'code',
            content: post.value.code.content || '',
            language: post.value.code.language || 'text',
        });
    }
    
    return blocks;
});

function getInlineCommentsIndex(blockId: number | string | null): Record<string, number> {
    if (!post.value?.inlineIndex && !post.value?.inline_comments_index) return {};
    const index = post.value.inlineIndex || post.value.inline_comments_index || {};
    const blockIdStr = String(blockId);
    return index[blockIdStr] || {};
}

// Fallback code content
const codeContent = computed(() => {
    if (!post.value) return '';
    if (post.value.snippets && Array.isArray(post.value.snippets) && post.value.snippets.length > 0) {
        return post.value.snippets[0].code_text || post.value.snippets[0].content || '';
    }
    return post.value.code?.content || '';
});

const codeLanguage = computed(() => {
    if (!post.value) return 'text';
    if (post.value.snippets && Array.isArray(post.value.snippets) && post.value.snippets.length > 0) {
        return post.value.snippets[0].language || 'text';
    }
    return post.value.code?.language || 'text';
});

const codeBlockId = computed(() => {
    if (!post.value) return null;
    if (post.value.snippets && Array.isArray(post.value.snippets) && post.value.snippets.length > 0) {
        return post.value.snippets[0].id || null;
    }
    return null;
});

const hasCodeButNoBlocks = computed(() => {
    return (!postBlocks.value || postBlocks.value.length === 0) && (codeContent.value || post.value?.code?.content);
});

function formatDate(dateString: string) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

onMounted(async () => {
    // Always fetch full post data from API to ensure we have snippets and complete data
    if (props.tab.postId) {
        loading.value = true;
        try {
            const response = await fetch(`/api/posts/${props.tab.postId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (response.ok) {
                const data = await response.json();
                // API returns { data: PostResource }
                post.value = data.data || data.post || data;
                console.log('Loaded post data from API:', post.value);
                console.log('Post blocks:', postBlocks.value);
                console.log('Post snippets:', post.value?.snippets);
            } else {
                const errorText = await response.text();
                console.error('Failed to load post:', response.status, errorText);
                
                // Fallback to provided post data if API fails
                if (props.tab.postData) {
                    post.value = props.tab.postData;
                    console.log('Using provided post data as fallback:', post.value);
                } else {
                    error.value = 'Failed to load post';
                }
            }
        } catch (e) {
            console.error('Error loading post:', e);
            
            // Fallback to provided post data if API fails
            if (props.tab.postData) {
                post.value = props.tab.postData;
                console.log('Using provided post data as fallback (error):', post.value);
            } else {
                error.value = 'Failed to load post';
            }
        } finally {
            loading.value = false;
        }
    } else if (props.tab.postData) {
        // Use provided post data if no postId
        post.value = props.tab.postData;
        console.log('Using provided post data:', post.value);
        console.log('Post blocks:', postBlocks.value);
        console.log('Post snippets:', post.value?.snippets);
    }
});
</script>

<style scoped>
.prose {
    color: #cccccc;
}
</style>

