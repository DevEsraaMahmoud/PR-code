<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center space-x-8">
                        <Link href="/" class="flex items-center space-x-2 group">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                <span class="text-white font-bold text-lg">PR</span>
                            </div>
                            <span class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                                PR Code
                            </span>
                        </Link>
                        <div class="hidden md:flex items-center space-x-6">
                            <Link href="/" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                Explore
                            </Link>
                            <Link v-if="$page.props.auth.user" href="/posts/create" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                Create Post
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <template v-if="$page.props.auth.user">
                            <span class="text-sm text-gray-600">{{ $page.props.auth.user.name }}</span>
                            <form @submit.prevent="logout" class="inline">
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                                    Logout
                                </button>
                            </form>
                        </template>
                        <template v-else>
                            <Link href="/login" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                                Login
                            </Link>
                            <Link href="/register" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-sm hover:shadow-md">
                                Sign Up
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash.message" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-sm">
                {{ $page.props.flash.message }}
            </div>
        </div>
        <div v-if="$page.props.flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-sm">
                {{ $page.props.flash.error }}
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-gray-600 text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} PR Code. Built with Laravel & Vue.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

const logout = () => {
    router.post('/logout');
};
</script>

