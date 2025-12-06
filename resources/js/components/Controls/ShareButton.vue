<template>
  <div class="relative">
    <button
      @click="toggleMenu"
      class="share-button flex items-center gap-2 px-3 py-1.5 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
      :aria-label="'Share this post'"
      :aria-expanded="showMenu"
      :aria-haspopup="true"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
      </svg>
      <span class="text-sm font-medium">Share</span>
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="showMenu"
        ref="menuRef"
        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-10"
        role="menu"
        aria-orientation="vertical"
      >
        <button
          @click="copyLink"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
          role="menuitem"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          {{ linkCopied ? 'Copied!' : 'Copy Link' }}
        </button>
        <button
          @click="shareTwitter"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
          role="menuitem"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
          </svg>
          Share on Twitter
        </button>
        <button
          v-if="canShareNative"
          @click="shareNative"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2"
          role="menuitem"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          Share via...
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  postId: number | string;
  postTitle?: string;
  postUrl?: string;
}>();

const emit = defineEmits<{
  shared: [{ method: string }];
}>();

const showMenu = ref(false);
const linkCopied = ref(false);
const menuRef = ref(null);
const canShareNative = ref(false);

const shareUrl = computed(() => {
  return props.postUrl || `${window.location.origin}/posts/${props.postId}`;
});

onMounted(() => {
  // Check if native Web Share API is available
  canShareNative.value = typeof navigator !== 'undefined' && 'share' in navigator;
});

function toggleMenu() {
  showMenu.value = !showMenu.value;
}

function handleClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    showMenu.value = false;
  }
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value);
    linkCopied.value = true;
    emit('shared', { method: 'copy' });
    setTimeout(() => {
      linkCopied.value = false;
      showMenu.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy link:', err);
  }
}

function shareTwitter() {
  const text = encodeURIComponent(props.postTitle || 'Check out this post');
  const url = encodeURIComponent(shareUrl.value);
  window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
  emit('shared', { method: 'twitter' });
  showMenu.value = false;
}

async function shareNative() {
  if (!canShareNative.value) return;
  
  try {
    await navigator.share({
      title: props.postTitle || 'PR Code Post',
      url: shareUrl.value,
    });
    emit('shared', { method: 'native' });
    showMenu.value = false;
  } catch (err) {
    if (err.name !== 'AbortError') {
      console.error('Failed to share:', err);
    }
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

