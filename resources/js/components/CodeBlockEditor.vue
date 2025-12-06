<template>
    <div class="border border-gray-300 rounded-md p-4 bg-white">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700">
                {{ block.type === 'code' ? 'Code Block' : 'Text Block' }}
            </span>
            <button
                v-if="blocks && blocks.length > 1"
                type="button"
                @click="$emit('remove', index)"
                class="text-red-600 hover:text-red-700 text-sm"
            >
                Remove
            </button>
        </div>
        <div v-if="block.type === 'code'" class="mb-2">
            <select
                :value="block.language"
                @change="updateLanguage"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="javascript">JavaScript</option>
                <option value="typescript">TypeScript</option>
                <option value="python">Python</option>
                <option value="php">PHP</option>
                <option value="java">Java</option>
                <option value="go">Go</option>
                <option value="rust">Rust</option>
                <option value="ruby">Ruby</option>
                <option value="cpp">C++</option>
                <option value="c">C</option>
                <option value="css">CSS</option>
                <option value="html">HTML</option>
                <option value="sql">SQL</option>
                <option value="json">JSON</option>
                <option value="xml">XML</option>
                <option value="yaml">YAML</option>
                <option value="bash">Bash</option>
                <option value="text">Plain Text</option>
            </select>
        </div>
        <textarea
            :value="block.content"
            @input="updateContent"
            :rows="block.type === 'code' ? 10 : 5"
            class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-sm focus:ring-blue-500 focus:border-blue-500"
            :placeholder="block.type === 'code' ? 'Enter code...' : 'Enter text...'"
        ></textarea>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

const props = defineProps<{
    block: { type: string; content: string; language?: string };
    index: number;
    blocks?: any[];
}>();

const emit = defineEmits<{
    update: [index: number, block: any];
    remove: [index: number];
}>();

const updateContent = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update', props.index, { ...props.block, content: target.value });
};

const updateLanguage = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('update', props.index, { ...props.block, language: target.value });
};
</script>

