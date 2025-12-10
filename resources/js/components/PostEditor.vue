<template>
  <div class="post-editor bg-[#252526] rounded-lg border border-[#3e3e42] p-6">
    <form @submit.prevent="handleSubmit">
      <!-- Title Input -->
      <div class="mb-4">
        <label for="post-title" class="block text-sm font-medium text-[#cccccc] mb-2">
          Title
        </label>
        <input
          id="post-title"
          v-model="formData.title"
          type="text"
          required
          placeholder="Enter post title..."
          class="w-full px-4 py-2 bg-[#1e1e1e] border border-[#3e3e42] rounded text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-2 focus:ring-[#007acc] focus:border-transparent"
        />
      </div>

      <!-- Content Editor Tabs -->
      <div class="mb-4">
        <div class="flex items-center gap-2 border-b border-[#3e3e42]">
          <button
            v-for="tab in editorTabs"
            :key="tab.value"
            type="button"
            @click="activeTab = tab.value"
            :class="[
              'px-4 py-2 text-sm font-medium transition-colors border-b-2',
              activeTab === tab.value
                ? 'border-[#007acc] text-[#007acc]'
                : 'border-transparent text-[#858585] hover:text-[#cccccc]'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- Text Editor -->
      <div v-if="activeTab === 'text'" class="mb-4">
        <label for="post-body" class="block text-sm font-medium text-[#cccccc] mb-2">
          Content
        </label>
        <textarea
          id="post-body"
          v-model="formData.body"
          rows="10"
          placeholder="Write your post content here..."
          class="w-full px-4 py-2 bg-[#1e1e1e] border border-[#3e3e42] rounded text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-2 focus:ring-[#007acc] focus:border-transparent resize-y font-mono text-sm"
          @input="handleAutosave"
        ></textarea>
        <p class="text-xs text-[#858585] mt-1">
          Markdown supported. Auto-saved {{ lastSaved ? formatTime(lastSaved) : 'never' }}
        </p>
      </div>

      <!-- Code Snippet Composer -->
      <div v-if="activeTab === 'code'" class="mb-4 space-y-4">
        <div>
          <label for="code-language" class="block text-sm font-medium text-[#cccccc] mb-2">
            Language
          </label>
          <select
            id="code-language"
            v-model="formData.codeLanguage"
            class="w-full px-4 py-2 bg-[#1e1e1e] border border-[#3e3e42] rounded text-[#cccccc] focus:outline-none focus:ring-2 focus:ring-[#007acc] focus:border-transparent"
          >
            <option value="text">Plain Text</option>
            <option value="javascript">JavaScript</option>
            <option value="typescript">TypeScript</option>
            <option value="python">Python</option>
            <option value="php">PHP</option>
            <option value="java">Java</option>
            <option value="go">Go</option>
            <option value="rust">Rust</option>
            <option value="ruby">Ruby</option>
            <option value="css">CSS</option>
            <option value="html">HTML</option>
            <option value="json">JSON</option>
            <option value="sql">SQL</option>
            <option value="bash">Bash</option>
          </select>
        </div>

        <div>
          <label for="code-content" class="block text-sm font-medium text-[#cccccc] mb-2">
            Code
          </label>
          <textarea
            id="code-content"
            v-model="formData.codeContent"
            rows="15"
            placeholder="Paste your code here..."
            class="w-full px-4 py-2 bg-[#1e1e1e] border border-[#3e3e42] rounded text-[#cccccc] placeholder-[#858585] focus:outline-none focus:ring-2 focus:ring-[#007acc] focus:border-transparent resize-y font-mono text-sm"
            @input="handleAutosave"
          ></textarea>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="handleFileUpload"
            class="px-4 py-2 text-sm bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e] transition-colors"
          >
            Upload File
          </button>
          <input
            ref="fileInputRef"
            type="file"
            class="hidden"
            @change="handleFileSelected"
          />
          <button
            type="button"
            @click="showPreview = !showPreview"
            class="px-4 py-2 text-sm bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e] transition-colors"
          >
            {{ showPreview ? 'Hide' : 'Show' }} Preview
          </button>
        </div>

        <!-- Code Preview -->
        <div v-if="showPreview && formData.codeContent" class="mt-4">
          <CodeViewer
            :snippet="{
              id: 'preview',
              language: formData.codeLanguage,
              content: formData.codeContent,
            }"
            :can-copy="false"
            :can-open-diff="false"
            :is-authenticated="false"
          />
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-4 border-t border-[#3e3e42]">
        <div class="text-xs text-[#858585]">
          <span v-if="saving">Saving draft...</span>
          <span v-else-if="lastSaved">Draft saved {{ formatTime(lastSaved) }}</span>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="$emit('cancel')"
            class="px-4 py-2 text-sm bg-[#3e3e42] text-[#cccccc] rounded hover:bg-[#4e4e4e] transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting || !isFormValid"
            class="px-4 py-2 text-sm bg-[#007acc] text-white rounded hover:bg-[#005a9e] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ submitting ? 'Publishing...' : 'Publish' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import CodeViewer from './CodeViewer.vue';

const props = defineProps<{
  postId?: number | string;
  initialData?: {
    title?: string;
    body?: string;
    code?: {
      language: string;
      content: string;
    };
  };
}>();

const emit = defineEmits<{
  'cancel': [];
  'saved': [postId: number | string];
}>();

const formData = ref({
  title: props.initialData?.title || '',
  body: props.initialData?.body || '',
  codeLanguage: props.initialData?.code?.language || 'text',
  codeContent: props.initialData?.code?.content || '',
});

const activeTab = ref<'text' | 'code'>('text');
const showPreview = ref(false);
const submitting = ref(false);
const saving = ref(false);
const lastSaved = ref<Date | null>(null);
const fileInputRef = ref<HTMLInputElement>();
const autosaveTimer = ref<number | null>(null);

const editorTabs = [
  { value: 'text', label: 'Text' },
  { value: 'code', label: 'Code' },
];

const isFormValid = computed(() => {
  return formData.value.title.trim().length > 0 &&
         (formData.value.body.trim().length > 0 || formData.value.codeContent.trim().length > 0);
});

function formatTime(date: Date): string {
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  
  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins} minute${diffMins !== 1 ? 's' : ''} ago`;
  return date.toLocaleTimeString();
}

function handleAutosave() {
  // Debounce autosave
  if (autosaveTimer.value) {
    clearTimeout(autosaveTimer.value);
  }
  
  autosaveTimer.value = window.setTimeout(() => {
    saveDraft();
  }, 2000);
}

function saveDraft() {
  saving.value = true;
  try {
    const draft = {
      title: formData.value.title,
      body: formData.value.body,
      codeLanguage: formData.value.codeLanguage,
      codeContent: formData.value.codeContent,
      timestamp: new Date().toISOString(),
    };
    localStorage.setItem('post-draft', JSON.stringify(draft));
    lastSaved.value = new Date();
  } catch (error) {
    console.error('Failed to save draft:', error);
  } finally {
    saving.value = false;
  }
}

function loadDraft() {
  try {
    const draftJson = localStorage.getItem('post-draft');
    if (draftJson) {
      const draft = JSON.parse(draftJson);
      formData.value = {
        title: draft.title || '',
        body: draft.body || '',
        codeLanguage: draft.codeLanguage || 'text',
        codeContent: draft.codeContent || '',
      };
    }
  } catch (error) {
    console.error('Failed to load draft:', error);
  }
}

function handleFileUpload() {
  fileInputRef.value?.click();
}

async function handleFileSelected(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;
  
  try {
    const text = await file.text();
    formData.value.codeContent = text;
    
    // Try to detect language from file extension
    const extension = file.name.split('.').pop()?.toLowerCase();
    const languageMap: Record<string, string> = {
      'js': 'javascript',
      'ts': 'typescript',
      'py': 'python',
      'php': 'php',
      'java': 'java',
      'go': 'go',
      'rs': 'rust',
      'rb': 'ruby',
      'css': 'css',
      'html': 'html',
      'json': 'json',
      'sql': 'sql',
      'sh': 'bash',
    };
    if (extension && languageMap[extension]) {
      formData.value.codeLanguage = languageMap[extension];
    }
  } catch (error) {
    console.error('Failed to read file:', error);
    alert('Failed to read file. Please try again.');
  }
}

async function handleSubmit() {
  if (!isFormValid.value || submitting.value) return;
  
  submitting.value = true;
  
  try {
    const payload: any = {
      title: formData.value.title,
      body: formData.value.body,
    };
    
    // Add code snippet if provided
    if (formData.value.codeContent.trim()) {
      payload.code = {
        language: formData.value.codeLanguage,
        content: formData.value.codeContent,
      };
    }
    
    // Use Inertia to submit
    if (props.postId) {
      // Update existing post
      router.put(`/posts/${props.postId}`, payload, {
        onSuccess: () => {
          clearDraft();
          emit('saved', props.postId!);
        },
        onError: (errors) => {
          console.error('Failed to update post:', errors);
        },
      });
    } else {
      // Create new post
      router.post('/api/v1/posts', payload, {
        onSuccess: (page) => {
          clearDraft();
          // Extract post ID from response
          const postId = (page.props as any).post?.id;
          if (postId) {
            emit('saved', postId);
          }
        },
        onError: (errors) => {
          console.error('Failed to create post:', errors);
        },
      });
    }
  } catch (error) {
    console.error('Failed to submit post:', error);
  } finally {
    submitting.value = false;
  }
}

function clearDraft() {
  localStorage.removeItem('post-draft');
  lastSaved.value = null;
}

onMounted(() => {
  if (!props.initialData) {
    loadDraft();
  }
});

onUnmounted(() => {
  if (autosaveTimer.value) {
    clearTimeout(autosaveTimer.value);
  }
});
</script>

<style scoped>
.post-editor {
  min-height: 400px;
}
</style>


