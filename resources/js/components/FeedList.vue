<template>
  <div class="feed-list">
    <!-- Sorting Controls -->
    <div v-if="showSorting" class="mb-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-sm text-[#858585]">Sort by:</span>
        <div class="flex items-center gap-1 bg-[#252526] border border-[#3e3e42] rounded-lg p-1">
          <button
            v-for="sortOption in sortOptions"
            :key="sortOption.value"
            @click="handleSortChange(sortOption.value)"
            :class="[
              'px-3 py-1 text-xs rounded transition-colors',
              currentSort === sortOption.value
                ? 'bg-[#007acc] text-white'
                : 'text-[#858585] hover:text-[#cccccc]'
            ]"
          >
            {{ sortOption.label }}
          </button>
        </div>
      </div>
      <div class="text-xs text-[#858585]">
        {{ totalCount !== undefined ? `${posts.length} of ${totalCount}` : `${posts.length} posts` }}
      </div>
    </div>

    <!-- Posts List -->
    <div v-if="posts.length === 0 && !loading" class="text-center py-12">
      <p class="text-[#858585] mb-2">No posts found</p>
      <p class="text-xs text-[#858585]">Be the first to share your code!</p>
    </div>

    <div v-else class="space-y-4">
      <PostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
      />
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#007acc]"></div>
      <p class="text-sm text-[#858585] mt-2">Loading more posts...</p>
    </div>

    <!-- Load More Trigger (Intersection Observer target) -->
    <div
      v-if="hasMore && !loading"
      ref="loadMoreTriggerRef"
      class="h-1"
      aria-hidden="true"
    ></div>

    <!-- End of Feed -->
    <div v-if="!hasMore && posts.length > 0" class="text-center py-8">
      <p class="text-sm text-[#858585]">You've reached the end of the feed</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import PostCard from './PostCard.vue';

const props = defineProps<{
  posts: any[];
  currentSort?: 'recent' | 'top' | 'trending';
  showSorting?: boolean;
  hasMore?: boolean;
  totalCount?: number;
  loading?: boolean;
}>();

const emit = defineEmits<{
  'sort-changed': [sort: 'recent' | 'top' | 'trending'];
  'load-more': [];
}>();

const loadMoreTriggerRef = ref<HTMLElement>();
const observer = ref<IntersectionObserver | null>(null);
const loading = ref(props.loading || false);

const sortOptions = [
  { value: 'recent', label: 'Recent' },
  { value: 'top', label: 'Top' },
  { value: 'trending', label: 'Trending' },
];

const currentSort = computed(() => props.currentSort || 'recent');

function handleSortChange(sort: 'recent' | 'top' | 'trending') {
  emit('sort-changed', sort);
  
  // Use Inertia to update URL and preserve scroll position
  router.get(window.location.pathname, {
    sort,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['posts'],
  });
}

function setupIntersectionObserver() {
  if (!loadMoreTriggerRef.value || !props.hasMore) return;
  
  observer.value = new IntersectionObserver(
    (entries) => {
      const entry = entries[0];
      if (entry.isIntersecting && props.hasMore && !loading.value) {
        emit('load-more');
      }
    },
    {
      root: null,
      rootMargin: '200px', // Start loading 200px before reaching the trigger
      threshold: 0.1,
    }
  );
  
  observer.value.observe(loadMoreTriggerRef.value);
}

onMounted(() => {
  setupIntersectionObserver();
});

onUnmounted(() => {
  if (observer.value) {
    observer.value.disconnect();
  }
});

watch(() => [props.hasMore, loadMoreTriggerRef.value], () => {
  if (observer.value) {
    observer.value.disconnect();
  }
  setupIntersectionObserver();
});
</script>

<style scoped>
.feed-list {
  min-height: 200px;
}
</style>


