<template>
    <div class="relative code-viewer" ref="containerRef">
        <div class="bg-gray-900 rounded-lg overflow-hidden border border-gray-700">
            <!-- Language Badge -->
            <div class="flex items-center justify-between bg-gray-800 px-4 py-2 border-b border-gray-700">
                <span class="text-xs font-medium text-gray-300 uppercase">{{ language }}</span>
                <div v-if="isAuthenticated" class="text-xs text-gray-400">
                    Click line numbers to select range
                </div>
            </div>

            <!-- Code Container -->
            <div class="flex overflow-x-auto">
                <!-- Line Numbers -->
                <div
                    class="flex-shrink-0 bg-gray-800 text-gray-400 text-right pr-4 py-2 select-none border-r border-gray-700"
                    ref="lineNumbersRef"
                >
                    <div
                        v-for="(line, index) in lines"
                        :key="index"
                        :data-line-number="index + 1"
                        class="leading-6 px-2 cursor-pointer hover:bg-gray-700 transition-colors"
                        :class="{
                            'bg-blue-900/40': isLineSelected(index + 1),
                            'bg-yellow-900/30': hasInlineComments(index + 1),
                        }"
                        @click="handleLineClick(index + 1)"
                        @mouseenter="handleLineHover(index + 1)"
                        @mouseleave="handleLineLeave"
                    >
                        {{ index + 1 }}
                    </div>
                </div>

                <!-- Code Content -->
                <div class="flex-1 overflow-x-auto relative" ref="codeContentRef">
                    <pre
                        ref="codeElementRef"
                        class="p-4 text-sm text-gray-100 m-0"
                        :class="`language-${language}`"
                    >
                        <code ref="codeRef">{{ codeText }}</code>
                    </pre>

                    <!-- Inline Comment Badges -->
                    <div
                        v-for="commentGroup in groupedInlineComments"
                        :key="`${commentGroup.startLine}-${commentGroup.endLine}`"
                        class="absolute right-4"
                        :style="{ top: `${(commentGroup.startLine - 1) * 24}px` }"
                    >
                        <InlineAnnotation
                            :comments="commentGroup.comments"
                            :start-line="commentGroup.startLine"
                            :end-line="commentGroup.endLine"
                            :line-height="24"
                            @view-comments="() => viewComments(commentGroup.startLine, commentGroup.endLine)"
                        />
                    </div>
                </div>
            </div>

            <!-- Selection Actions -->
            <div
                v-if="selectedRange.start > 0 && selectedRange.end > 0"
                class="bg-blue-900/20 border-t border-blue-700 px-4 py-2 flex items-center justify-between"
            >
                <span class="text-sm text-blue-200">
                    Selected: Lines {{ selectedRange.start }}-{{ selectedRange.end }}
                </span>
                <div class="flex space-x-2">
                    <button
                        @click="clearSelection"
                        class="px-3 py-1 text-xs text-gray-300 hover:text-white"
                    >
                        Clear
                    </button>
                    <button
                        v-if="isAuthenticated"
                        @click="openCommentModal"
                        class="px-4 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                    >
                        Add Comment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import Prism from 'prismjs';
import 'prismjs/themes/prism-tomorrow.css';
import InlineAnnotation from './InlineAnnotation.vue';

// Import language definitions
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-typescript';
import 'prismjs/components/prism-python';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-java';
import 'prismjs/components/prism-go';
import 'prismjs/components/prism-rust';
import 'prismjs/components/prism-ruby';
import 'prismjs/components/prism-cpp';
import 'prismjs/components/prism-c';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-yaml';
import 'prismjs/components/prism-bash';

const props = defineProps<{
    codeText: string;
    language: string;
    snippetId: number;
    comments?: any[];
    isAuthenticated?: boolean;
}>();

const emit = defineEmits<{
    comment: [snippetId: number, startLine: number, endLine: number];
    'view-comments': [startLine: number, endLine: number];
}>();

const containerRef = ref<HTMLElement>();
const lineNumbersRef = ref<HTMLElement>();
const codeContentRef = ref<HTMLElement>();
const codeElementRef = ref<HTMLElement>();
const codeRef = ref<HTMLElement>();

const selectedRange = ref({ start: 0, end: 0 });
const selectionStart = ref<number | null>(null);
const hoveredLine = ref<number | null>(null);

const lines = computed(() => props.codeText.split('\n'));

const inlineComments = computed(() => {
    return (props.comments || []).filter((c: any) => c.is_inline && c.snippet_id === props.snippetId);
});

const groupedInlineComments = computed(() => {
    const groups: Record<string, any> = {};
    
    inlineComments.value.forEach((comment: any) => {
        const key = `${comment.start_line}-${comment.end_line}`;
        if (!groups[key]) {
            groups[key] = {
                startLine: comment.start_line,
                endLine: comment.end_line,
                comments: [],
            };
        }
        groups[key].comments.push(comment);
    });

    return Object.values(groups);
});

const isLineSelected = (lineNumber: number) => {
    return selectedRange.value.start <= lineNumber && lineNumber <= selectedRange.value.end;
};

const hasInlineComments = (lineNumber: number) => {
    return inlineComments.value.some((c: any) => 
        c.start_line <= lineNumber && lineNumber <= c.end_line
    );
};

const handleLineClick = (lineNumber: number) => {
    if (!props.isAuthenticated) return;

    if (!selectionStart.value) {
        selectionStart.value = lineNumber;
        selectedRange.value = { start: lineNumber, end: lineNumber };
    } else {
        if (lineNumber < selectionStart.value) {
            selectedRange.value = { start: lineNumber, end: selectionStart.value };
        } else {
            selectedRange.value = { start: selectionStart.value, end: lineNumber };
        }
    }
};

const handleLineHover = (lineNumber: number) => {
    hoveredLine.value = lineNumber;
    if (selectionStart.value) {
        if (lineNumber < selectionStart.value) {
            selectedRange.value = { start: lineNumber, end: selectionStart.value };
        } else {
            selectedRange.value = { start: selectionStart.value, end: lineNumber };
        }
    }
};

const handleLineLeave = () => {
    hoveredLine.value = null;
};

const clearSelection = () => {
    selectedRange.value = { start: 0, end: 0 };
    selectionStart.value = null;
};

const openCommentModal = () => {
    if (selectedRange.value.start > 0 && selectedRange.value.end > 0) {
        emit('comment', props.snippetId, selectedRange.value.start, selectedRange.value.end);
        clearSelection();
    }
};

const viewComments = (startLine: number, endLine: number) => {
    emit('view-comments', startLine, endLine);
};

const highlightCode = async () => {
    await nextTick();
    if (codeRef.value) {
        Prism.highlightElement(codeRef.value);
    }
};

onMounted(() => {
    highlightCode();
});

watch(() => props.codeText, () => {
    highlightCode();
});

watch(() => props.language, () => {
    highlightCode();
});
</script>

<style scoped>
pre[class*="language-"] {
    margin: 0;
    background: transparent;
}

.code-viewer :deep(.token) {
    color: inherit;
}
</style>

