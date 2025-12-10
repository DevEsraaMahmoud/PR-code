<template>
  <AppLayout>
    <div class="feed-page p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-[#cccccc]">PR Code Feed</h1>
        <Link
          v-if="$page.props.auth?.user"
          href="/posts/create"
          class="px-4 py-2 bg-gradient-to-r from-[#007acc] to-purple-600 text-white rounded-lg hover:from-[#005a9e] hover:to-purple-700 transition-all shadow-sm hover:shadow-md font-medium"
        >
          + New Post
        </Link>
      </div>

      <!-- Feed List with Sorting and Infinite Scroll -->
      <FeedList
        :posts="posts"
        :current-sort="currentSort"
        :show-sorting="true"
        :has-more="hasMore"
        :total-count="totalCount"
        :loading="loading"
        @sort-changed="handleSortChange"
        @load-more="handleLoadMore"
      />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FeedList from '@/components/FeedList.vue';

const props = defineProps<{
  posts?: any[];
  currentSort?: 'recent' | 'top' | 'trending';
  hasMore?: boolean;
  totalCount?: number;
  links?: any;
}>();

const loading = ref(false);
const currentSort = computed(() => props.currentSort || 'recent');

function handleSortChange(sort: 'recent' | 'top' | 'trending') {
  router.get('/feed', {
    sort,
    page: 1,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['posts', 'currentSort', 'hasMore', 'totalCount'],
  });
}

function handleLoadMore() {
  if (loading.value || !props.hasMore) return;
  
  loading.value = true;
  
  // Get current page from URL or default to 1
  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = parseInt(urlParams.get('page') || '1', 10);
  const nextPage = currentPage + 1;
  
  router.get('/feed', {
    sort: currentSort.value,
    page: nextPage,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['posts', 'hasMore', 'totalCount'],
    onFinish: () => {
      loading.value = false;
    },
  });
}
</script>

<style scoped>
.feed-page {
  min-height: calc(100vh - 200px);
}
</style>
