<template>
  <button
    @click="handleClick"
    :disabled="pending"
    class="follow-button px-4 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="[
      isFollowing
        ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
        : 'bg-blue-600 text-white hover:bg-blue-700',
    ]"
    :aria-label="isFollowing ? 'Unfollow this user' : 'Follow this user'"
    :aria-pressed="isFollowing"
  >
    {{ isFollowing ? 'Following' : 'Follow' }}
  </button>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  userId: number | string;
  initialFollowing?: boolean;
}>();

const emit = defineEmits<{
  followed: [];
  unfollowed: [];
}>();

const isFollowing = ref(props.initialFollowing);
const pending = ref(false);

function handleClick() {
  if (pending.value) return;

  const wasFollowing = isFollowing.value;
  isFollowing.value = !isFollowing.value;
  pending.value = true;

  const action = wasFollowing ? 'unfollow' : 'follow';
  
  router.post(
    `/users/${props.userId}/${action}`,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        if (wasFollowing) {
          emit('unfollowed');
        } else {
          emit('followed');
        }
        pending.value = false;
      },
      onError: () => {
        // Revert on error
        isFollowing.value = wasFollowing;
        pending.value = false;
      },
    }
  );
}
</script>

