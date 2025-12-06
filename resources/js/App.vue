<template>
    <div id="app" class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/" class="text-xl font-bold text-gray-900">
                            PR Code
                        </router-link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <router-link
                            v-if="authStore.isAuthenticated"
                            to="/posts/create"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Create Post
                        </router-link>
                        <router-link
                            v-if="!authStore.isAuthenticated"
                            to="/login"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Login
                        </router-link>
                        <router-link
                            v-if="!authStore.isAuthenticated"
                            to="/register"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
                        >
                            Register
                        </router-link>
                        <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4">
                            <span class="text-gray-700">{{ authStore.user?.name }}</span>
                            <button
                                @click="handleLogout"
                                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();

const handleLogout = async () => {
    await authStore.logout();
};
</script>

