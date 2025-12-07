<template>
    <div class="border border-[#3e3e42] rounded p-4 bg-[#252526]">
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm font-medium text-[#cccccc]">
                {{ block.type === 'code' ? 'Code Block' : 'Text Block' }}
            </span>
            <button
                v-if="blocks && blocks.length > 1"
                type="button"
                @click="$emit('remove', index)"
                class="text-red-400 hover:text-red-300 text-sm transition-colors"
            >
                Remove
            </button>
        </div>
        <div v-if="block.type === 'code'" class="mb-3">
            <select
                :value="block.language"
                @change="updateLanguage"
                class="px-3 py-1.5 border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] text-sm focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] transition-all"
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
            class="w-full px-3 py-2 border border-[#3e3e42] rounded bg-[#1e1e1e] text-[#cccccc] placeholder-[#858585] font-mono text-sm focus:ring-1 focus:ring-[#007acc] focus:border-[#007acc] transition-all resize-none"
            :placeholder="block.type === 'code' ? 'Enter code...' : 'Enter text...'"
            style="line-height: 1.35;"
        ></textarea>
    </div>
</template>

<script setup lang="ts">
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

<style scoped>
select option {
    background-color: #1e1e1e;
    color: #cccccc;
}
</style>

