<template>
    <div class="max-w-md mx-auto mt-16">
        <div class="bg-gray-800 rounded-lg shadow-md p-8 border border-gray-700">
            <h2 class="text-2xl font-bold text-gray-100 mb-6">Register</h2>
            <form @submit.prevent="handleRegister">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                        Name
                    </label>
                    <input
                        id="name"
                        v-model="name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Password
                    </label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        required
                        minlength="8"
                        class="w-full px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                        Confirm Password
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="passwordConfirmation"
                        type="password"
                        required
                        minlength="8"
                        class="w-full px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div v-if="error" class="mb-4 text-red-400 text-sm">{{ error }}</div>
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ loading ? 'Registering...' : 'Register' }}
                </button>
            </form>
            <p class="mt-4 text-center text-sm text-gray-400">
                Already have an account?
                <router-link to="/login" class="text-blue-400 hover:text-blue-300 hover:underline">Login</router-link>
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Passwords do not match';
        return;
    }
    loading.value = true;
    error.value = '';
    try {
        await authStore.register(name.value, email.value, password.value, passwordConfirmation.value);
        router.push('/');
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Registration failed';
    } finally {
        loading.value = false;
    }
};
</script>

