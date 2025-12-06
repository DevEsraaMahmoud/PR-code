<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div v-if="postsStore.loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="!postsStore.currentPost" class="text-center py-12">
            <p class="text-gray-500">Post not found.</p>
        </div>

        <div v-else class="flex gap-6">
            <div class="flex-1">
                <div class="bg-gray-800 rounded-lg shadow p-6 mb-6 border border-gray-700">
                    <h1 class="text-3xl font-bold text-gray-100 mb-2">
                        {{ postsStore.currentPost.title }}
                    </h1>
                    <p class="text-sm text-gray-400 mb-6">
                        By {{ postsStore.currentPost.user.name }} • {{ formatDate(postsStore.currentPost.created_at) }}
                    </p>

                    <div class="prose max-w-none prose-invert">
                        <div
                            v-for="(block, index) in postsStore.currentPost.body"
                            :key="index"
                            class="mb-6"
                        >
                            <div v-if="block.type === 'text'" class="text-gray-300 whitespace-pre-wrap">
                                {{ block.content }}
                            </div>
                            <div v-else-if="block.type === 'code'">
                                <CodeSnippetViewer
                                    :code-text="getSnippetCode(block)"
                                    :language="getSnippetLanguage(block)"
                                    :snippet-id="getSnippetId(block)"
                                    :comments="getSnippetComments(block)"
                                    :is-authenticated="authStore.isAuthenticated"
                                    @comment="(snippetId, startLine, endLine) => handleCommentClick(snippetId, startLine, endLine)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <InlineCommentSidebar
                v-if="selectedSnippetId"
                :comments="snippetComments"
                :is-authenticated="authStore.isAuthenticated"
                @reply="handleReply"
            />
        </div>

        <AddInlineCommentModal
            :show="showCommentModal"
            :start-line="selectedStartLine"
            :end-line="selectedEndLine"
            :snippet-id="selectedSnippetId || 0"
            :parent-comment="parentComment"
            @submit="handleCommentSubmit"
            @close="closeCommentModal"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { usePostsStore } from '../stores/posts';
import { useCommentsStore } from '../stores/comments';
import { useAuthStore } from '../stores/auth';
import CodeSnippetViewer from '../components/CodeSnippetViewer.vue';
import InlineCommentSidebar from '../components/InlineCommentSidebar.vue';
import AddInlineCommentModal from '../components/AddInlineCommentModal.vue';

const route = useRoute();
const postsStore = usePostsStore();
const commentsStore = useCommentsStore();
const authStore = useAuthStore();

const selectedSnippetId = ref<number | null>(null);
const selectedStartLine = ref<number>(0);
const selectedEndLine = ref<number>(0);
const showCommentModal = ref(false);
const parentComment = ref<any>(null);

const snippetComments = computed(() => {
    if (!selectedSnippetId.value) return [];
    return commentsStore.comments;
});

const getSnippetCode = (block: any) => {
    const snippet = postsStore.currentPost?.snippets?.find(
        (s) => s.block_index === postsStore.currentPost?.body.indexOf(block)
    );
    return snippet?.code_text || '';
};

const getSnippetLanguage = (block: any) => {
    const snippet = postsStore.currentPost?.snippets?.find(
        (s) => s.block_index === postsStore.currentPost?.body.indexOf(block)
    );
    return snippet?.language || block.language || 'text';
};

const getSnippetId = (block: any) => {
    const snippet = postsStore.currentPost?.snippets?.find(
        (s) => s.block_index === postsStore.currentPost?.body.indexOf(block)
    );
    return snippet?.id || 0;
};

const getSnippetComments = (block: any) => {
    const snippet = postsStore.currentPost?.snippets?.find(
        (s) => s.block_index === postsStore.currentPost?.body.indexOf(block)
    );
    return snippet?.comments || [];
};

const handleCommentClick = (snippetId: number, startLine: number, endLine: number) => {
    selectedSnippetId.value = snippetId;
    selectedStartLine.value = startLine;
    selectedEndLine.value = endLine;
    showCommentModal.value = true;
    parentComment.value = null;
    // Load comments for this snippet
    commentsStore.fetchComments(snippetId);
};

const handleReply = (commentId: number) => {
    const comment = commentsStore.comments.find((c) => c.id === commentId) ||
        commentsStore.comments.flatMap((c) => c.replies || []).find((r) => r.id === commentId);
    if (comment) {
        parentComment.value = comment;
        showCommentModal.value = true;
    }
};

const handleCommentSubmit = async (data: any) => {
    try {
        await commentsStore.createComment(data);
        await postsStore.fetchPost(Number(route.params.id));
        closeCommentModal();
    } catch (error) {
        console.error('Failed to create comment:', error);
    }
};

const closeCommentModal = () => {
    showCommentModal.value = false;
    parentComment.value = null;
    selectedStartLine.value = 0;
    selectedEndLine.value = 0;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

onMounted(async () => {
    const postId = Number(route.params.id);
    await postsStore.fetchPost(postId);
    
    // Load comments for all snippets
    if (postsStore.currentPost?.snippets) {
        for (const snippet of postsStore.currentPost.snippets) {
            await commentsStore.fetchComments(snippet.id);
        }
    }
});

watch(() => postsStore.currentPost, (post) => {
    if (post?.snippets && post.snippets.length > 0) {
        selectedSnippetId.value = post.snippets[0].id;
        commentsStore.fetchComments(post.snippets[0].id);
    }
});
</script>

