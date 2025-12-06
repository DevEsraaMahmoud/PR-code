<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mb-4 shadow-lg">
                    <span class="text-white font-bold text-2xl">PR</span>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Create account</h2>
                <p class="mt-2 text-gray-600">Join PR Code today</p>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <form @submit.prevent="submit">
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                    </div>
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <div v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</div>
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <div v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</div>
                    </div>
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition-all disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating account...' : 'Create account' }}
                    </button>
                </form>
                <p class="mt-6 text-center text-sm text-gray-600">
                    Already have an account?
                    <Link href="/login" class="font-medium text-blue-600 hover:text-blue-700">
                        Sign in
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        preserveScroll: true,
    });
};

defineProps<{
    errors?: Record<string, string>;
}>();
</script>

