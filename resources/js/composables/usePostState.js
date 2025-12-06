/**
 * Pinia store for post state management and optimistic updates
 * Handles likes, comments, and other post interactions
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export const usePostStore = defineStore('post', () => {
  // State
  const likedPosts = ref(new Set());
  const pendingLikes = ref(new Set());
  const pendingComments = ref(new Map()); // postId -> array of pending comments
  const optimisticStats = ref(new Map()); // postId -> { likes, comments }

  // Getters
  const isLiked = computed(() => (postId) => {
    return likedPosts.value.has(postId);
  });

  const getOptimisticStats = computed(() => (postId, baseStats) => {
    const optimistic = optimisticStats.value.get(postId);
    if (!optimistic) return baseStats;
    return {
      ...baseStats,
      likes: baseStats.likes + optimistic.likes,
      comments: baseStats.comments + optimistic.comments,
    };
  });

  // Actions
  function toggleLike(postId, currentLiked = false) {
    const wasLiked = likedPosts.value.has(postId) || currentLiked;
    const isNowLiked = !wasLiked;

    // Optimistic update
    if (isNowLiked) {
      likedPosts.value.add(postId);
      pendingLikes.value.add(postId);
    } else {
      likedPosts.value.delete(postId);
      pendingLikes.value.delete(postId);
    }

    // Update optimistic stats
    const currentOptimistic = optimisticStats.value.get(postId) || { likes: 0, comments: 0 };
    optimisticStats.value.set(postId, {
      ...currentOptimistic,
      likes: isNowLiked ? currentOptimistic.likes + 1 : currentOptimistic.likes - 1,
    });

    // Make server request
    router.post(
      `/posts/${postId}/like`,
      {},
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          pendingLikes.value.delete(postId);
        },
        onError: () => {
          // Revert optimistic update on error
          if (isNowLiked) {
            likedPosts.value.delete(postId);
            const optimistic = optimisticStats.value.get(postId);
            if (optimistic) {
              optimisticStats.value.set(postId, {
                ...optimistic,
                likes: optimistic.likes - 1,
              });
            }
          } else {
            likedPosts.value.add(postId);
            const optimistic = optimisticStats.value.get(postId);
            if (optimistic) {
              optimisticStats.value.set(postId, {
                ...optimistic,
                likes: optimistic.likes + 1,
              });
            }
          }
          pendingLikes.value.delete(postId);
        },
      }
    );
  }

  function addOptimisticComment(postId, comment) {
    const pending = pendingComments.value.get(postId) || [];
    pending.push({
      ...comment,
      id: `pending-${Date.now()}-${Math.random()}`,
      isPending: true,
    });
    pendingComments.value.set(postId, pending);

    // Update optimistic stats
    const currentOptimistic = optimisticStats.value.get(postId) || { likes: 0, comments: 0 };
    optimisticStats.value.set(postId, {
      ...currentOptimistic,
      comments: currentOptimistic.comments + 1,
    });
  }

  function removeOptimisticComment(postId, tempId) {
    const pending = pendingComments.value.get(postId) || [];
    const filtered = pending.filter((c) => c.id !== tempId);
    pendingComments.value.set(postId, filtered);

    // Update optimistic stats
    const currentOptimistic = optimisticStats.value.get(postId) || { likes: 0, comments: 0 };
    optimisticStats.value.set(postId, {
      ...currentOptimistic,
      comments: Math.max(0, currentOptimistic.comments - 1),
    });
  }

  function clearOptimisticComments(postId) {
    pendingComments.value.delete(postId);
  }

  function syncPostLiked(postId, isLiked) {
    if (isLiked) {
      likedPosts.value.add(postId);
    } else {
      likedPosts.value.delete(postId);
    }
  }

  function resetOptimisticStats(postId) {
    optimisticStats.value.delete(postId);
  }

  return {
    // State
    likedPosts,
    pendingLikes,
    pendingComments,
    optimisticStats,
    // Getters
    isLiked,
    getOptimisticStats,
    // Actions
    toggleLike,
    addOptimisticComment,
    removeOptimisticComment,
    clearOptimisticComments,
    syncPostLiked,
    resetOptimisticStats,
  };
});

