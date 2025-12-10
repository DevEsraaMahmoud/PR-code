<template>
  <div class="reactions relative inline-block">
    <!-- Reaction Button / Summary -->
    <button
      type="button"
      @click.prevent="toggleMenu"
      class="reaction-button flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all hover:bg-[#3e3e42] cursor-pointer"
      :class="{
        'bg-[#3e3e42]': isMenuOpen,
        'text-[#007acc]': hasAnyReaction,
        'text-[#858585]': !hasAnyReaction,
      }"
      :aria-label="hasAnyReaction ? 'Change reaction' : 'Add reaction'"
      :aria-expanded="isMenuOpen"
    >
      <!-- Show current reaction or default icon -->
      <span v-if="currentReaction" class="text-lg">
        {{ getReactionEmoji(currentReaction) }}
      </span>
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="text-sm font-medium">{{ reactionLabel }}</span>
      <span v-if="totalCount > 0" class="text-xs text-[#858585]">({{ totalCount }})</span>
    </button>

    <!-- Reaction Menu Popup -->
    <div
      v-show="isMenuOpen"
      ref="menuRef"
      class="reaction-menu absolute bottom-full left-0 mb-2 bg-[#252526] border border-[#3e3e42] rounded-full px-2 py-1 shadow-xl flex items-center gap-1"
      style="z-index: 9999;"
      @click.stop
    >
      <button
        v-for="reactionType in reactionTypes"
        :key="reactionType"
        type="button"
        @click.stop="handleReactionClick(reactionType)"
        :disabled="pending"
        class="reaction-icon-button w-10 h-10 rounded-full flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:scale-110 active:scale-95 cursor-pointer"
        :class="getReactionButtonClass(reactionType)"
        :title="getReactionLabel(reactionType)"
        :aria-label="`${hasReaction(reactionType) ? 'Remove' : 'Add'} ${reactionType} reaction`"
      >
        <span class="text-xl">{{ getReactionEmoji(reactionType) }}</span>
      </button>
    </div>

    <!-- Backdrop (for click outside) -->
    <div
      v-show="isMenuOpen"
      class="fixed inset-0"
      style="z-index: 9998;"
      @click="closeMenu"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  targetId: number | string;
  targetType: 'post' | 'comment';
  initialReactions?: Array<{
    id: number;
    type: string;
    user_id: number;
  }>;
  currentUserId?: number;
  showLabel?: boolean;
}>();

const emit = defineEmits<{
  'reaction-changed': [{ targetId: number | string; reactions: any[]; counts: Record<string, number> }];
}>();

const reactionTypes = ['like', 'clap', 'heart', 'love', 'lightbulb', 'laugh'];
const reactions = ref<Array<{ id: number; type: string; user_id: number }>>(props.initialReactions || []);
const pending = ref(false);
const optimisticReactions = ref<Array<{ type: string; user_id: number; pending: boolean }>>([]);
const isMenuOpen = ref(false);
const menuRef = ref<HTMLElement>();

const totalCount = computed(() => {
  return reactions.value.length + optimisticReactions.value.filter(r => !r.pending).length;
});

const reactionCounts = computed(() => {
  const counts: Record<string, number> = {};
  reactionTypes.forEach(type => {
    counts[type] = reactions.value.filter(r => r.type === type).length;
  });
  optimisticReactions.value.forEach(r => {
    if (!r.pending) {
      counts[r.type] = (counts[r.type] || 0) + 1;
    }
  });
  return counts;
});

const currentReaction = computed(() => {
  if (!props.currentUserId) return null;
  const userReaction = reactions.value.find(r => r.user_id === props.currentUserId);
  if (userReaction) return userReaction.type;
  const optimisticReaction = optimisticReactions.value.find(r => r.user_id === props.currentUserId && !r.pending);
  if (optimisticReaction) return optimisticReaction.type;
  return null;
});

const reactionLabel = computed(() => {
  if (currentReaction.value) {
    return getReactionLabel(currentReaction.value);
  }
  return 'React';
});

function getReactionEmoji(type: string): string {
  const emojis: Record<string, string> = {
    like: '👍',
    clap: '👏',
    heart: '❤️',
    love: '❤️',
    lightbulb: '💡',
    laugh: '😂',
  };
  return emojis[type] || '👍';
}

function getReactionLabel(type: string): string {
  const labels: Record<string, string> = {
    like: 'Like',
    clap: 'Clap',
    heart: 'Heart',
    love: 'Love',
    lightbulb: 'Lightbulb',
    laugh: 'Laugh',
  };
  return labels[type] || type;
}

function getReactionButtonClass(type: string): string {
  const classes: Record<string, string> = {
    like: 'bg-blue-500 hover:bg-blue-600',
    clap: 'bg-green-500 hover:bg-green-600',
    heart: 'bg-purple-400 hover:bg-purple-500',
    love: 'bg-red-500 hover:bg-red-600',
    lightbulb: 'bg-yellow-500 hover:bg-yellow-600',
    laugh: 'bg-blue-400 hover:bg-blue-500',
  };
  
  const baseClass = classes[type] || 'bg-[#3e3e42] hover:bg-[#4e4e4e]';
  
  if (hasReaction(type)) {
    return `${baseClass} ring-2 ring-white ring-offset-2 ring-offset-[#252526]`;
  }
  
  return baseClass;
}

const hasAnyReaction = computed(() => {
  return currentReaction.value !== null;
});

function hasReaction(type: string): boolean {
  if (!props.currentUserId) return false;
  return reactions.value.some(r => r.type === type && r.user_id === props.currentUserId) ||
         optimisticReactions.value.some(r => r.type === type && r.user_id === props.currentUserId && !r.pending);
}

function toggleMenu() {
  console.log('Toggle menu clicked, current state:', isMenuOpen.value);
  isMenuOpen.value = !isMenuOpen.value;
  console.log('Menu state after toggle:', isMenuOpen.value);
}

function closeMenu() {
  isMenuOpen.value = false;
}

async function handleReactionClick(type: string) {
  if (pending.value || !props.currentUserId) return;
  
  const hasReactionValue = hasReaction(type);
  pending.value = true;
  
  // Close menu after selection
  closeMenu();
  
  // Optimistic update
  if (hasReactionValue) {
    // Remove reaction optimistically
    const index = reactions.value.findIndex(r => r.type === type && r.user_id === props.currentUserId);
    if (index > -1) {
      reactions.value.splice(index, 1);
    }
    const optimisticIndex = optimisticReactions.value.findIndex(r => r.type === type && r.user_id === props.currentUserId);
    if (optimisticIndex > -1) {
      optimisticReactions.value.splice(optimisticIndex, 1);
    }
  } else {
    // Remove any existing reaction first
    const existingIndex = reactions.value.findIndex(r => r.user_id === props.currentUserId);
    if (existingIndex > -1) {
      reactions.value.splice(existingIndex, 1);
    }
    const optimisticExistingIndex = optimisticReactions.value.findIndex(r => r.user_id === props.currentUserId);
    if (optimisticExistingIndex > -1) {
      optimisticReactions.value.splice(optimisticExistingIndex, 1);
    }
    
    // Add new reaction optimistically
    optimisticReactions.value.push({
      type,
      user_id: props.currentUserId,
      pending: true,
    });
  }
  
  try {
    // Emit event for parent to handle API call
    const updatedReactions = [...reactions.value];
    if (!hasReactionValue) {
      // Add optimistic reaction to main list after API success
      optimisticReactions.value.forEach(or => {
        if (or.type === type && or.user_id === props.currentUserId) {
          or.pending = false;
        }
      });
    }
    
    emit('reaction-changed', {
      targetId: props.targetId,
      reactions: updatedReactions,
      counts: reactionCounts.value,
    });
    
  } catch (error) {
    // Rollback on error
    if (hasReactionValue) {
      reactions.value.push({
        id: 0,
        type,
        user_id: props.currentUserId!,
      });
    } else {
      const index = optimisticReactions.value.findIndex(r => r.type === type && r.user_id === props.currentUserId);
      if (index > -1) {
        optimisticReactions.value.splice(index, 1);
      }
    }
    console.error('Failed to toggle reaction:', error);
  } finally {
    pending.value = false;
  }
}

watch(() => props.initialReactions, (newReactions) => {
  if (newReactions) {
    reactions.value = [...newReactions];
    optimisticReactions.value = optimisticReactions.value.filter(or => or.pending);
  }
}, { deep: true });

// Watch menu state for debugging
watch(isMenuOpen, (newVal) => {
  console.log('Menu state changed to:', newVal);
  if (newVal && menuRef.value) {
    console.log('Menu element:', menuRef.value);
    console.log('Menu computed styles:', window.getComputedStyle(menuRef.value));
  }
});
</script>

<style scoped>
.reaction-menu {
  min-width: max-content;
  animation: slideUp 0.2s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(8px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.reaction-icon-button {
  transition: transform 0.1s, box-shadow 0.1s;
}

.reaction-icon-button:hover:not(:disabled) {
  transform: scale(1.1);
}

.reaction-icon-button:active:not(:disabled) {
  transform: scale(0.95);
}
</style>
