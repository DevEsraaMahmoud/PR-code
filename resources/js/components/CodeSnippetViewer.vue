<template>
    <div class="relative">
        <div
            ref="codeContainer"
            class="relative bg-gray-900 rounded-lg overflow-hidden"
            @click="handleCodeClick"
        >
            <div class="flex">
                <div class="flex-shrink-0 bg-gray-800 text-gray-400 text-right pr-4 py-2 select-none">
                    <div
                        v-for="(line, index) in lines"
                        :key="index"
                        :data-line-number="index + 1"
                        class="leading-6"
                        :class="{
                            'bg-blue-900/30': selectedLines.start <= index + 1 && index + 1 <= selectedLines.end,
                        }"
                    >
                        {{ index + 1 }}
                    </div>
                </div>
                <div class="flex-1 overflow-x-auto">
                    <pre
                        ref="codeElement"
                        class="p-4 text-sm text-gray-100"
                        :class="`language-${language}`"
                    ><code>{{ codeText }}</code></pre>
                </div>
            </div>
        </div>
        <div
            v-if="showCommentButton"
            class="absolute right-4 top-4"
        >
            <button
                @click="openCommentModal"
                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
            >
                Add Comment
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import Prism from 'prismjs';
import 'prismjs/themes/prism-tomorrow.css';

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
    comment: [startLine: number, endLine: number];
}>();

const codeContainer = ref<HTMLElement>();
const codeElement = ref<HTMLElement>();
const selectedLines = ref({ start: 0, end: 0 });
const showCommentButton = ref(false);

const lines = computed(() => props.codeText.split('\n'));

let startLine: number | null = null;

const handleCodeClick = (event: MouseEvent) => {
    if (!props.isAuthenticated) return;

    const target = event.target as HTMLElement;
    const lineNumberElement = target.closest('[data-line-number]');
    
    if (!lineNumberElement) return;

    const lineNumber = parseInt(lineNumberElement.getAttribute('data-line-number') || '0');

    if (!startLine) {
        startLine = lineNumber;
        selectedLines.value = { start: lineNumber, end: lineNumber };
        showCommentButton.value = true;
    } else {
        if (lineNumber < startLine) {
            selectedLines.value = { start: lineNumber, end: startLine };
        } else {
            selectedLines.value = { start: startLine, end: lineNumber };
        }
    }
};

const openCommentModal = () => {
    if (selectedLines.value.start > 0 && selectedLines.value.end > 0) {
        emit('comment', props.snippetId, selectedLines.value.start, selectedLines.value.end);
        selectedLines.value = { start: 0, end: 0 };
        startLine = null;
        showCommentButton.value = false;
    }
};

const highlightCode = async () => {
    await nextTick();
    if (codeElement.value) {
        Prism.highlightElement(codeElement.value);
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
</style>

