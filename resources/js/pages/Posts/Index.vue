<template>
    <AppLayout>
        <div>
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">All Posts</h1>
                <Link v-if="$page.props.auth.user" href="/posts/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Post
                </Link>
            </div>
            <!-- Same content as Home.vue -->
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    posts: any[];
}>();
</script>

