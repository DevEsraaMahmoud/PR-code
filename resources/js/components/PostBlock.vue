<template>
    <div class="post-block space-y-6">
        <!-- Text Blocks -->
        <div
            v-for="(block, index) in textBlocks"
            :key="`text-${index}`"
            class="bg-white rounded-xl shadow-md p-6 border border-gray-100 prose max-w-none"
        >
            <p class="text-gray-700 whitespace-pre-wrap">{{ block.content }}</p>
        </div>

        <!-- Code Blocks -->
        <div
            v-for="(block, index) in codeBlocks"
            :key="`code-${index}`"
            class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100"
        >
            <CodeViewer
                :code="getSnippetCode(block)"
                :language="getSnippetLanguage(block)"
                :inline-comments-index="getInlineCommentsIndex(block)"
                :is-authenticated="isAuthenticated"
                @line-selected="handleLineSelected"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import CodeViewer from './CodeViewer.vue';

const props = defineProps<{
    post: any;
    isAuthenticated?: boolean;
}>();

const emit = defineEmits<{
    'line-selected': [{ line: number; comments: any[]; snippetId: number }];
}>();

const textBlocks = computed(() => {
    const blocks = (props.post?.body || []).filter((block: any) => block.type === 'text');
    console.log('Text blocks:', blocks);
    return blocks;
});

const codeBlocks = computed(() => {
    const blocks = (props.post?.body || []).filter((block: any) => block.type === 'code');
    console.log('Code blocks:', blocks);
    console.log('Snippets:', props.post?.snippets);
    return blocks;
});

const getSnippetCode = (block: any): string => {
    if (!Array.isArray(props.post.snippets)) {
        console.warn('Snippets is not an array:', props.post.snippets);
        return block.content || '';
    }
    const snippet = props.post.snippets.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.code_text || block.content || '';
};

const getSnippetLanguage = (block: any): string => {
    if (!Array.isArray(props.post.snippets)) {
        return block.language || 'text';
    }
    const snippet = props.post.snippets.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.language || block.language || 'text';
};

const getSnippetId = (block: any): number => {
    if (!Array.isArray(props.post.snippets)) {
        return 0;
    }
    const snippet = props.post.snippets.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    return snippet?.id || 0;
};

const getInlineCommentsIndex = (block: any): Record<number, any[]> => {
    if (!Array.isArray(props.post.snippets)) {
        return {};
    }
    
    const snippet = props.post.snippets.find(
        (s: any) => s.block_index === props.post.body.indexOf(block)
    );
    
    if (!snippet) return {};
    
    const index: Record<number, any[]> = {};
    
    // Get inline comments for this snippet
    const snippetComments = Array.isArray(snippet.comments) 
        ? snippet.comments 
        : (Array.isArray(snippet.allComments) ? snippet.allComments : []);
    const inlineComments = snippetComments.filter(
        (c: any) => c.is_inline && c.start_line && c.end_line
    );
    
    inlineComments.forEach((comment: any) => {
        for (let line = comment.start_line; line <= comment.end_line; line++) {
            if (!index[line]) {
                index[line] = [];
            }
            // Avoid duplicates
            if (!index[line].some((c: any) => c.id === comment.id)) {
                index[line].push(comment);
            }
        }
    });
    
    return index;
};

const handleLineSelected = (data: { line: number; comments: any[] }) => {
    if (!Array.isArray(props.post.snippets)) {
        emit('line-selected', { ...data, snippetId: 0 });
        return;
    }
    
    // Find which snippet this belongs to
    const blockIndex = codeBlocks.value.findIndex((block: any) => {
        const snippet = props.post.snippets.find(
            (s: any) => s.block_index === props.post.body.indexOf(block)
        );
        if (!snippet) return false;
        const snippetComments = Array.isArray(snippet.comments) 
            ? snippet.comments 
            : (Array.isArray(snippet.allComments) ? snippet.allComments : []);
        const inlineComments = snippetComments.filter(
            (c: any) => c.is_inline
        );
        return inlineComments.some((c: any) => 
            c.start_line <= data.line && data.line <= c.end_line
        );
    });
    
    const block = codeBlocks.value[blockIndex];
    const snippetId = block ? getSnippetId(block) : 0;
    
    emit('line-selected', { ...data, snippetId });
};
</script>

<style scoped>
.post-block {
    /* Additional styles if needed */
}
</style>

