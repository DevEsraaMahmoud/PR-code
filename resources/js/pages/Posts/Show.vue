<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Post Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-8 mb-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ post?.title }}</h1>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    {{ post.user?.name?.charAt(0).toUpperCase() || '?' }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ post.user?.name || 'Unknown User' }}</p>
                                    <p class="text-sm">{{ post?.created_at ? formatDate(post.created_at) : '' }}</p>
                                </div>
                            </div>
                            <div v-if="post?.tags && post.tags.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="canEdit && post?.id" class="flex space-x-2">
                        <Link :href="`/posts/${post.id}/edit`" class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout: Post Content + Inline Thread Sidebar -->
            <div class="lg:grid lg:grid-cols-[1fr_320px] lg:gap-6">
                <!-- Left Column: Post Content -->
                <div class="space-y-6">
                    <!-- Render blocks in order -->
                    <div v-if="postBlocks && postBlocks.length > 0" class="space-y-6">
                        <template v-for="(block, index) in postBlocks" :key="`block-${block.id || index}`">
                            <!-- Text Block -->
                            <div
                                v-if="block.type === 'text' && block.content"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
                            >
                                <div class="prose prose-lg dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                    {{ block.content }}
                                </div>
                            </div>
                            
                            <!-- Code Block -->
                            <div
                                v-else-if="block.type === 'code' && block.content"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
                            >
                                <CodeSnippetCompact
                                    :code="block.content || ''"
                                    :language="block.language || 'text'"
                                    :block-id="block.id"
                                    :post-id="post.id"
                                    :inline-comments-index="getInlineCommentsIndex(block.id)"
                                    :is-feed="false"
                                    @line-clicked="handleLineClick"
                                />
                            </div>
                        </template>
                    </div>

                    <!-- Fallback: Old format -->
                    <div v-else class="space-y-6">
                        <!-- Rich text body -->
                        <div v-if="postBodyHtml" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {{ postBodyHtml }}
                            </div>
                        </div>

                        <!-- Code Block -->
                        <div v-if="postCode && postCode.content && postCode.content.trim()" class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-100 dark:border-gray-700">
                            <CodeSnippetCompact
                                :code="postCode.content"
                                :language="postCode.language || 'text'"
                                :block-id="postSnippetId || postCodeBlockId"
                                :post-id="post.id"
                                :inline-comments-index="getInlineCommentsIndex(postSnippetId || postCodeBlockId)"
                                :is-feed="false"
                                @line-clicked="handleLineClick"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="post?.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <LikeButton
                                :post-id="post.id"
                                :initial-liked="post.is_liked || false"
                                :initial-count="post.likes_count || post.stats?.likes || 0"
                            />
                            <ShareButton
                                :post-id="post.id"
                                :post-title="post.title || ''"
                            />
                            <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 ml-auto">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ post.views || post.stats?.views || 0 }} views</span>
                            </div>
                        </div>
                    </div>

                    <!-- General Comments (only non-inline comments) -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Comments</h2>
                        <CommentList
                            :comments="generalComments"
                            :post-id="post?.id"
                            :current-user-id="(page.props as any).auth?.user?.id"
                            @comment-added="handleCommentAdded"
                        />
                    </div>
                </div>

                <!-- Right Column: Inline Thread Sidebar (Desktop) -->
                <div class="hidden lg:block">
                    <!-- Sidebar will be positioned fixed on the right -->
                </div>
            </div>
        </div>

        <!-- Inline Thread Sidebar -->
        <InlineThreadSidebar
            :visible="sidebarVisible"
            :active-thread="activeThread"
            :current-user-id="(page.props as any).auth?.user?.id"
            :can-resolve="canEdit || true"
            @close="sidebarVisible = false"
            @thread-updated="handleThreadUpdated"
            @thread-resolved="handleThreadResolved"
        />

        <!-- Inline Comment Popover -->
        <InlineCommentPopover
            v-if="popoverVisible && selectedLine"
            :visible="popoverVisible"
            :line-number="selectedLine"
            :comments="getCommentsForLine(selectedLine)"
            :position="popoverPosition"
            @close="closePopover"
            @submit-comment="handlePopoverSubmit"
            @view-thread="handleViewThread"
        />
        
        <!-- Backdrop for popover (mobile) -->
        <div
            v-if="popoverVisible"
            class="fixed inset-0 bg-black/20 z-[9998] lg:hidden"
            @click="closePopover"
        ></div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CodeSnippetCompact from '@/Components/CodeSnippetCompact.vue';
import InlineThreadSidebar from '@/Components/InlineThreadSidebar.vue';
import InlineCommentPopover from '@/Components/CodeBlock/InlineCommentPopover.vue';
import CommentList from '@/Components/Comments/CommentList.vue';
import LikeButton from '@/Components/Controls/LikeButton.vue';
import ShareButton from '@/Components/Controls/ShareButton.vue';
import { router, Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    post: any;
}>();

const page = usePage();

const canEdit = computed(() => {
    return props.post?.user_id === (page.props as any).auth?.user?.id;
});

// Post blocks (new format)
const postBlocks = computed(() => {
    // Check if post.body is an array of blocks
    if (Array.isArray(props.post?.body) && props.post.body.length > 0) {
        return props.post.body.map((block: any, index: number) => {
            // Try to match with snippet ID if it's a code block
            let blockId = block.id;
            if (block.type === 'code' && !blockId && props.post?.snippets) {
                const snippet = props.post.snippets.find((s: any) => s.block_index === index);
                if (snippet) {
                    blockId = snippet.id;
                }
            }
            return {
                id: blockId || `${block.type}-${index + 1}`,
                type: block.type,
                content: block.content || '',
                language: block.language || 'text',
            };
        });
    }
    
    // Check if post.blocks exists (new format)
    if (Array.isArray(props.post?.blocks) && props.post.blocks.length > 0) {
        return props.post.blocks.map((block: any, index: number) => {
            // Try to match with snippet ID if it's a code block
            let blockId = block.id;
            if (block.type === 'code' && !blockId && props.post?.snippets) {
                const snippet = props.post.snippets.find((s: any) => s.block_index === index);
                if (snippet) {
                    blockId = snippet.id;
                }
            }
            return {
                ...block,
                id: blockId || block.id || `${block.type}-${index + 1}`,
            };
        });
    }
    
    // Fallback: create blocks from old format
    const blocks: any[] = [];
    if (props.post?.body || props.post?.body_html) {
        // If body is a string, treat it as text
        if (typeof props.post.body === 'string' || typeof props.post.body_html === 'string') {
            blocks.push({
                id: `text-${props.post.id}`,
                type: 'text',
                content: props.post.body || props.post.body_html || '',
            });
        }
    }
    if (props.post?.code?.content || props.post?.snippets) {
        const codeBlock = props.post.code || (props.post.snippets?.[0] ? {
            language: props.post.snippets[0].language,
            content: props.post.snippets[0].code_text || props.post.snippets[0].content,
        } : null);
        if (codeBlock && codeBlock.content) {
            blocks.push({
                id: props.post.snippets?.[0]?.id || `code-1`,
                type: 'code',
                content: codeBlock.content || '',
                language: codeBlock.language || 'text',
            });
        }
    }
    return blocks;
});

// Extract body HTML from body blocks if it's an array (fallback)
const postBodyHtml = computed(() => {
    if (Array.isArray(props.post?.body)) {
        return props.post.body
            .filter((block: any) => block.type === 'text')
            .map((block: any) => block.content)
            .join('\n\n');
    }
    return props.post?.body_html || props.post?.body || '';
});

// Extract snippet ID for inline comments
const postSnippetId = computed(() => {
    if (props.post?.snippets && Array.isArray(props.post.snippets) && props.post.snippets.length > 0) {
        return props.post.snippets[0].id || null;
    }
    return null;
});

const postCodeBlockId = computed(() => {
    if (postBlocks.value.length > 0) {
        const codeBlock = postBlocks.value.find((b: any) => b.type === 'code');
        return codeBlock?.id || null;
    }
    return postSnippetId.value;
});

// Extract code from snippets or use post.code (fallback)
const postCode = computed(() => {
    if (postBlocks.value.length > 0) {
        const codeBlock = postBlocks.value.find((b: any) => b.type === 'code');
        if (codeBlock) {
            return {
                language: codeBlock.language || 'text',
                content: codeBlock.content || '',
            };
        }
    }
    
    // Fallback to old format
    if (props.post?.code?.content) {
        return props.post.code;
    }
    
    if (props.post?.snippets && Array.isArray(props.post.snippets) && props.post.snippets.length > 0) {
        const snippet = props.post.snippets[0];
        return {
            language: snippet.language || 'text',
            content: snippet.code_text || snippet.content || ''
        };
    }
    
    return null;
});

// Inline comments index: { blockId: { lineNumber: commentCount } }
const inlineCommentsIndex = computed(() => {
    // Get base index from backend (preferred: inlineIndex, fallback: inline_comments_index)
    const backendIndex = props.post?.inlineIndex || props.post?.inline_comments_index || {};
    
    // Start with backend index (should already be in correct format: { blockId: { lineNumber: count } })
    const convertedIndex: Record<string, Record<string, number>> = {};
    
    // Process backend index
    if (backendIndex && typeof backendIndex === 'object') {
        Object.keys(backendIndex).forEach((key) => {
            const value = backendIndex[key];
            
            // Check if it's the new format: { blockId: { lineNumber: count } }
            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                // New format - use as is
                convertedIndex[key] = {};
                Object.keys(value).forEach((lineNum) => {
                    const count = typeof value[lineNum] === 'number' 
                        ? value[lineNum] 
                        : (Array.isArray(value[lineNum]) ? value[lineNum].length : 0);
                    if (count > 0) {
                        convertedIndex[key][lineNum] = count;
                    }
                });
            } else if (Array.isArray(value) || typeof value === 'number') {
                // Old format: { lineNumber: [comments] } - convert it
                const blockId = postCodeBlockId.value || postSnippetId.value || 'default';
                if (!convertedIndex[blockId]) {
                    convertedIndex[blockId] = {};
                }
                const count = Array.isArray(value) ? value.length : value;
                if (count > 0) {
                    convertedIndex[blockId][key] = count;
                }
            }
        });
    }
    
    // Also build index from snippets if available
    if (props.post?.snippets && Array.isArray(props.post.snippets)) {
        props.post.snippets.forEach((snippet: any) => {
            const snippetId = String(snippet.id);
            if (!convertedIndex[snippetId]) {
                convertedIndex[snippetId] = {};
            }
            
            const comments = snippet.comments || snippet.allComments || [];
            comments.forEach((comment: any) => {
                if (comment.is_inline && comment.start_line) {
                    const lineNum = String(comment.start_line);
                    convertedIndex[snippetId][lineNum] = (convertedIndex[snippetId][lineNum] || 0) + 1;
                    // Count replies too
                    if (comment.replies && Array.isArray(comment.replies)) {
                        convertedIndex[snippetId][lineNum] += comment.replies.length;
                    }
                }
            });
        });
    }
    
    // Also build index from active threads to ensure we have current data
    if (activeThread.value && activeThread.value.blockId) {
        const blockId = String(activeThread.value.blockId);
        if (!convertedIndex[blockId]) {
            convertedIndex[blockId] = {};
        }
        
        // Count comments per line from thread messages
        activeThread.value.messages.forEach((msg: any) => {
            const lineNum = String(msg.line_number || activeThread.value.lineNumber);
            convertedIndex[blockId][lineNum] = (convertedIndex[blockId][lineNum] || 0) + 1;
        });
    }
    
    return convertedIndex;
});

function getInlineCommentsIndex(blockId: number | string | null): Record<string, number> {
    if (!blockId) {
        // Try to find any block with comments
        const blockIds = Object.keys(inlineCommentsIndex.value);
        if (blockIds.length > 0) {
            return inlineCommentsIndex.value[blockIds[0]] || {};
        }
        return {};
    }
    
    const blockIdStr = String(blockId);
    let index = inlineCommentsIndex.value[blockIdStr] || {};
    
    // If no index found, try to match with snippet IDs
    if (Object.keys(index).length === 0 && props.post?.snippets) {
        // Try direct snippet ID match
        const snippet = props.post.snippets.find((s: any) => String(s.id) === blockIdStr);
        if (snippet) {
            index = inlineCommentsIndex.value[String(snippet.id)] || {};
        }
        
        // If still no match and blockId is like "code-1", try to find by block_index
        if (Object.keys(index).length === 0 && blockIdStr.startsWith('code-')) {
            const blockIndex = parseInt(blockIdStr.replace('code-', '')) - 1;
            const snippetByIndex = props.post.snippets.find((s: any) => s.block_index === blockIndex);
            if (snippetByIndex) {
                index = inlineCommentsIndex.value[String(snippetByIndex.id)] || {};
            }
        }
        
        // If still no match, use first snippet's index
        if (Object.keys(index).length === 0 && props.post.snippets.length > 0) {
            index = inlineCommentsIndex.value[String(props.post.snippets[0].id)] || {};
        }
    }
    
    // Also check if we have thread data for this block
    if (activeThread.value && String(activeThread.value.blockId) === blockIdStr) {
        const threadIndex: Record<string, number> = { ...index };
        activeThread.value.messages.forEach((msg: any) => {
            const lineNum = String(msg.line_number || activeThread.value.lineNumber);
            threadIndex[lineNum] = (threadIndex[lineNum] || 0) + 1;
        });
        return threadIndex;
    }
    
    return index;
}

// Inline thread sidebar state
const sidebarVisible = ref(false);
const activeThread = ref<any>(null);
const popoverVisible = ref(false);
const selectedLine = ref<number | null>(null);
const popoverPosition = ref({ top: 0, left: 0 });

// General comments (non-inline only)
const generalComments = computed(() => {
    const comments = props.post?.comments || [];
    // Filter out inline comments if they're mixed in
    return comments.filter((c: any) => !c.is_inline);
});

async function handleLineClick({ lineNumber, blockId, postId }: { lineNumber: number; blockId: number | string | null; postId: number | string }) {
    selectedLine.value = lineNumber;
    
    // Load thread first to get existing comments (but don't open sidebar automatically)
    await loadThread(lineNumber, blockId, postId);
    
    // Show popover only (user can choose to view thread via button)
    await nextTick();
    await nextTick(); // Double nextTick to ensure DOM is updated
    
    // Try multiple selectors to find the line element
    const selectors = [
        `[data-line-number="${lineNumber}"]`,
        `.code-line[data-line-number="${lineNumber}"]`,
        `.line-number-column[aria-label*="Line ${lineNumber}"]`,
    ];
    
    let lineElement: Element | null = null;
    for (const selector of selectors) {
        lineElement = document.querySelector(selector);
        if (lineElement) break;
    }
    
    if (lineElement) {
        const rect = lineElement.getBoundingClientRect();
        popoverPosition.value = {
            top: rect.top + window.scrollY + rect.height / 2 - 100, // Center popover vertically
            left: Math.min(rect.right + window.scrollX + 20, window.innerWidth - 340), // Ensure it fits on screen
        };
    } else {
        // Fallback: position near center of viewport
        popoverPosition.value = {
            top: window.scrollY + window.innerHeight / 2 - 150,
            left: window.scrollX + window.innerWidth / 2 - 160,
        };
    }
    
    popoverVisible.value = true;
}

function handleViewThread() {
    // Open sidebar when user clicks "View Thread" button in popover
    sidebarVisible.value = true;
    // Optionally close popover when opening sidebar
    // popoverVisible.value = false;
}

async function loadThread(lineNumber: number, blockId: number | string | null, postId: number | string) {
    if (!blockId) return;
    
    try {
        const response = await fetch(`/posts/${postId}/blocks/${blockId}/threads?line=${lineNumber}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            console.log('Thread loaded:', data); // Debug log
            activeThread.value = {
                lineNumber,
                blockId,
                postId,
                messages: Array.isArray(data.messages) ? data.messages : (Array.isArray(data) ? data : []),
                resolved: data.resolved || false,
            };
            
            // The computed inlineCommentsIndex will automatically pick up the thread data
        } else {
            const errorText = await response.text();
            console.error('Failed to load thread:', response.status, errorText);
            // Create empty thread structure
            activeThread.value = {
                lineNumber,
                blockId,
                postId,
                messages: [],
                resolved: false,
            };
        }
    } catch (error) {
        console.error('Failed to load thread:', error);
        // Create empty thread structure
        activeThread.value = {
            lineNumber,
            blockId,
            postId,
            messages: [],
            resolved: false,
        };
    }
}

// Watch activeThread to update highlighting when thread loads
watch(activeThread, (newThread) => {
    if (newThread && newThread.messages && newThread.messages.length > 0) {
        // Force reactivity update for highlighting
        // The computed inlineCommentsIndex will pick up the changes automatically
        console.log('Thread updated, messages count:', newThread.messages.length);
    }
}, { deep: true });

function getCommentsForLine(lineNumber: number | null): any[] {
    if (!lineNumber || !activeThread.value) return [];
    // Return all messages for this line (both parent comments and replies)
    return activeThread.value.messages.filter((m: any) => 
        m.line_number === lineNumber || m.line_number === null || m.line_number === undefined
    );
}

function closePopover() {
    popoverVisible.value = false;
    selectedLine.value = null;
}

async function handlePopoverSubmit({ lineNumber, text }: { lineNumber: number; text: string }) {
    if (!activeThread.value) return;
    
    try {
        await router.post(`/posts/${activeThread.value.postId}/blocks/${activeThread.value.blockId}/threads`, {
            line: lineNumber,
            content: text,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Reload thread to show the new comment in both popover and sidebar
                loadThread(lineNumber, activeThread.value!.blockId, activeThread.value!.postId);
                // Keep popover open to show the new comment
            },
            onError: (errors) => {
                console.error('Failed to submit comment:', errors);
            },
        });
    } catch (error) {
        console.error('Failed to submit comment:', error);
    }
}

function handleThreadResolved({ resolved }: { resolved: boolean }) {
    if (activeThread.value) {
        activeThread.value.resolved = resolved;
    }
}

// Reload thread when updated
function handleThreadUpdated() {
    if (activeThread.value) {
        loadThread(activeThread.value.lineNumber, activeThread.value.blockId, activeThread.value.postId);
    }
}

const handleCommentAdded = () => {
    // Reload post data to get new comments
    router.reload({ only: ['post'] });
};

// Check URL params for line/blockId (from feed navigation)
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const line = urlParams.get('line');
    const blockId = urlParams.get('blockId');
    
    if (line && blockId && props.post?.id) {
        handleLineClick({
            lineNumber: parseInt(line),
            blockId,
            postId: props.post.id,
        });
    }
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
