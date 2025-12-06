<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Post Header -->
            <div class="bg-white rounded-xl shadow-md p-8 mb-6 border border-gray-100">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ post.title }}</h1>
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex items-center text-gray-600">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    {{ post.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ post.user.name }}</p>
                                    <p class="text-sm">{{ formatDate(post.created_at) }}</p>
                                </div>
                            </div>
                            <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="canEdit" class="flex space-x-2">
                        <Link :href="`/posts/${post.id}/edit`" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Post Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <div
                        v-for="(block, index) in post.body"
                        :key="index"
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100"
                    >
                        <div v-if="block.type === 'text'" class="p-6 prose max-w-none">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ block.content }}</p>
                        </div>
                        <div v-else-if="block.type === 'code'" class="relative">
                            <CodeViewer
                                :code-text="getSnippetCode(block)"
                                :language="getSnippetLanguage(block)"
                                :snippet-id="getSnippetId(block)"
                                :comments="getSnippetComments(block)"
                                :is-authenticated="!!$page.props.auth.user"
                                @comment="handleCommentClick"
                                @view-comments="handleViewComments"
                            />
                        </div>
                    </div>

                    <!-- Regular Comments Section -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Comments</h2>
                        <div v-if="regularComments.length === 0" class="text-gray-500 text-center py-8">
                            No comments yet. Be the first to comment!
                        </div>
                        <CommentThread
                            v-else
                            :comments="regularComments"
                            :is-authenticated="!!$page.props.auth.user"
                            :current-user-id="$page.props.auth.user?.id"
                            @reply="handleReply"
                            @edit="handleEdit"
                            @delete="handleDelete"
                            @like="handleLike"
                        />
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div v-if="selectedSnippetId" class="bg-white rounded-xl shadow-md p-6 border border-gray-100 sticky top-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Inline Comments</h3>
                        <div v-if="snippetComments.length === 0" class="text-gray-500 text-sm">
                            No inline comments yet.
                        </div>
                        <CommentThread
                            v-else
                            :comments="snippetComments"
                            :is-authenticated="!!$page.props.auth.user"
                            :current-user-id="$page.props.auth.user?.id"
                            @reply="handleReply"
                            @edit="handleEdit"
                            @delete="handleDelete"
                            @like="handleLike"
                        />
                    </div>
                </div>
            </div>

            <!-- Comment Modal -->
            <AddInlineCommentModal
                :show="showCommentModal"
                :start-line="selectedStartLine"
                :end-line="selectedEndLine"
                :snippet-id="selectedSnippetId || 0"
                :post-id="post.id"
                :parent-comment="parentComment"
                @submit="handleCommentSubmit"
                @close="closeCommentModal"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CodeViewer from '@/Components/CodeViewer.vue';
import CommentThread from '@/Components/CommentThread.vue';
import AddInlineCommentModal from '@/Components/AddInlineCommentModal.vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps<{
    post: any;
}>();

const selectedSnippetId = ref<number | null>(null);
const selectedStartLine = ref<number>(0);
const selectedEndLine = ref<number>(0);
const showCommentModal = ref(false);
const parentComment = ref<any>(null);

const canEdit = computed(() => {
    return props.post.user_id === (window as any).$page?.props?.auth?.user?.id;
});

const regularComments = computed(() => {
    return props.post.comments || [];
});

const snippetComments = computed(() => {
    if (!selectedSnippetId.value) return [];
    const snippet = props.post.snippets?.find((s: any) => s.id === selectedSnippetId.value);
    return snippet?.comments?.filter((c: any) => c.is_inline) || [];
});

const getSnippetCode = (block: any) => {
    const snippet = props.post.snippets?.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.code_text || '';
};

const getSnippetLanguage = (block: any) => {
    const snippet = props.post.snippets?.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.language || block.language || 'text';
};

const getSnippetId = (block: any) => {
    const snippet = props.post.snippets?.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.id || 0;
};

const getSnippetComments = (block: any) => {
    const snippet = props.post.snippets?.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.comments || [];
};

const handleCommentClick = (snippetId: number, startLine: number, endLine: number) => {
    selectedSnippetId.value = snippetId;
    selectedStartLine.value = startLine;
    selectedEndLine.value = endLine;
    showCommentModal.value = true;
    parentComment.value = null;
};

const handleViewComments = (startLine: number, endLine: number) => {
    selectedSnippetId.value = props.post.snippets?.find((s: any) => 
        s.comments?.some((c: any) => c.start_line === startLine && c.end_line === endLine)
    )?.id || null;
};

const handleReply = (commentId: number, body?: string) => {
    if (body) {
        // Submit reply
        router.post('/comments', {
            post_id: props.post.id,
            parent_id: commentId,
            body: body,
            is_inline: false,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['post'] });
            },
        });
    } else {
        // Open reply form
        const allComments = [
            ...regularComments.value,
            ...props.post.snippets?.flatMap((s: any) => s.comments || []) || []
        ];
        const comment = allComments.find((c: any) => c.id === commentId) ||
            allComments.flatMap((c: any) => c.replies || []).find((r: any) => r.id === commentId);
        if (comment) {
            parentComment.value = comment;
            showCommentModal.value = true;
        }
    }
};

const handleEdit = (comment: any) => {
    // Implement edit functionality
    console.log('Edit comment', comment);
};

const handleDelete = (commentId: number) => {
    router.delete(`/comments/${commentId}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['post'] });
        },
    });
};

const handleLike = (commentId: number) => {
    router.post(`/comments/${commentId}/like`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['post'] });
        },
    });
};

const handleCommentSubmit = async (data: any) => {
    router.post('/comments', data, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['post'] });
            closeCommentModal();
        },
        onError: (errors) => {
            console.error('Failed to create comment:', errors);
        },
    });
};

const closeCommentModal = () => {
    showCommentModal.value = false;
    parentComment.value = null;
    selectedStartLine.value = 0;
    selectedEndLine.value = 0;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

// Laravel Echo - Listen for new comments
onMounted(() => {
    if ((window as any).Echo && props.post.id) {
        const channel = (window as any).Echo.private(`post.${props.post.id}`);
        
        channel.listen('.comment.created', (data: any) => {
            // Reload post data to get new comment
            router.reload({ only: ['post'], preserveScroll: true });
        });
    }
});

onUnmounted(() => {
    if ((window as any).Echo && props.post.id) {
        (window as any).Echo.leave(`post.${props.post.id}`);
    }
});
</script>
