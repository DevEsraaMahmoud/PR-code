<template>
    <div class="relative code-viewer" ref="containerRef">
        <div class="bg-[#0d1117] rounded-lg overflow-hidden border border-gray-700 shadow-lg">
            <!-- Language Badge -->
            <div class="flex items-center justify-between bg-[#161b22] px-4 py-2 border-b border-gray-700">
                <span class="text-xs font-medium text-gray-300 uppercase">{{ language }}</span>
                <div v-if="isAuthenticated" class="text-xs text-gray-400">
                    Click lines to comment
                </div>
            </div>

            <!-- Code Container -->
            <div class="flex overflow-x-auto relative">
                <!-- Line Numbers Gutter -->
                <div
                    class="flex-shrink-0 bg-[#161b22] text-gray-500 text-right pr-4 pl-4 py-2 select-none border-r border-gray-700"
                    ref="lineNumbersRef"
                >
                    <div
                        v-for="(line, index) in lines"
                        :key="index"
                        :data-line="index + 1"
                        class="relative leading-6 min-h-[24px] cursor-pointer hover:bg-gray-800/50 transition-colors"
                        :class="{
                            'bg-blue-900/30': selectedLine === index + 1,
                            'bg-yellow-900/20': hasCommentsOnLine(index + 1),
                        }"
                        @click="handleLineClick(index + 1)"
                        @keydown.enter="handleLineClick(index + 1)"
                        :tabindex="isAuthenticated ? 0 : -1"
                        :aria-label="`Line ${index + 1}${hasCommentsOnLine(index + 1) ? `, ${getCommentCount(index + 1)} comment${getCommentCount(index + 1) > 1 ? 's' : ''}` : ''}`"
                    >
                        <span class="text-xs">{{ index + 1 }}</span>
                        <InlineCommentBubble
                            v-if="hasCommentsOnLine(index + 1)"
                            :line-number="index + 1"
                            :comment-count="getCommentCount(index + 1)"
                            :has-comments="true"
                            @click.stop="handleViewComments(index + 1)"
                        />
                    </div>
                </div>

                <!-- Code Content -->
                <div class="flex-1 overflow-x-auto relative" ref="codeContentRef">
                    <div
                        ref="codeElementRef"
                        class="p-4 text-xs font-mono"
                        v-html="highlightedCode"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import InlineCommentBubble from './InlineCommentBubble.vue';
import { highlightCode } from '@/utils/shiki-init';

const props = defineProps<{
    code: string;
    language: string;
    inlineCommentsIndex?: Record<number, any[]>;
    isAuthenticated?: boolean;
}>();

const emit = defineEmits<{
    'line-selected': [{ line: number; comments: any[] }];
}>();

const containerRef = ref<HTMLElement>();
const lineNumbersRef = ref<HTMLElement>();
const codeContentRef = ref<HTMLElement>();
const codeElementRef = ref<HTMLElement>();

const selectedLine = ref<number | null>(null);
const highlightedCode = ref<string>('');

const lines = computed(() => (props.code || '').split('\n'));

const inlineCommentsIndex = computed(() => props.inlineCommentsIndex || {});

const hasCommentsOnLine = (lineNumber: number): boolean => {
    return !!inlineCommentsIndex.value[lineNumber]?.length;
};

const getCommentCount = (lineNumber: number): number => {
    return inlineCommentsIndex.value[lineNumber]?.length || 0;
};

const getCommentsForLine = (lineNumber: number): any[] => {
    return inlineCommentsIndex.value[lineNumber] || [];
};

const handleLineClick = (lineNumber: number) => {
    if (!props.isAuthenticated) return;
    
    selectedLine.value = lineNumber;
    const comments = getCommentsForLine(lineNumber);
    emit('line-selected', { line: lineNumber, comments });
};

const handleViewComments = (lineNumber: number) => {
    const comments = getCommentsForLine(lineNumber);
    emit('line-selected', { line: lineNumber, comments });
};

const updateHighlighting = async () => {
    if (!codeElementRef.value) return;
    
    try {
        const html = await highlightCode(props.code || '', props.language || 'text');
        highlightedCode.value = html;
        
        // Apply custom styles for VSCode-like theme
        await nextTick();
        const codeElement = codeElementRef.value;
        if (codeElement) {
            // Ensure proper styling
            const preElement = codeElement.querySelector('pre');
            if (preElement) {
                preElement.className = 'm-0 p-0 bg-transparent';
                preElement.style.margin = '0';
                preElement.style.padding = '0';
            }
        }
    } catch (error) {
        console.warn('Failed to highlight code:', error);
        // Fallback to plain text
        highlightedCode.value = `<pre class="m-0 p-0"><code>${escapeHtml(props.code || '')}</code></pre>`;
    }
};

const escapeHtml = (text: string): string => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

onMounted(() => {
    updateHighlighting();
});

watch(() => props.code, () => {
    updateHighlighting();
});

watch(() => props.language, () => {
    updateHighlighting();
});
</script>

<style scoped>
.code-viewer :deep(pre) {
    margin: 0;
    padding: 0;
    background: transparent;
    font-size: 0.75rem;
    line-height: 1.5rem;
}

.code-viewer :deep(code) {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.75rem;
    line-height: 1.5rem;
}

.code-viewer :deep(.line) {
    display: block;
    min-height: 24px;
}

/* Shiki theme styles are injected automatically */
</style>
