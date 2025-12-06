import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../composables/useApi';

interface Post {
    id: number;
    title: string;
    body: any[];
    user: {
        id: number;
        name: string;
        email: string;
    };
    snippets: Snippet[];
    created_at: string;
    updated_at: string;
}

interface Snippet {
    id: number;
    post_id: number;
    language: string;
    code_text: string;
    block_index: number;
    comments: Comment[];
}

interface Comment {
    id: number;
    snippet_id: number;
    parent_id: number | null;
    start_line: number;
    end_line: number;
    body: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    replies: Comment[];
    created_at: string;
}

export const usePostsStore = defineStore('posts', () => {
    const posts = ref<Post[]>([]);
    const currentPost = ref<Post | null>(null);
    const loading = ref(false);

    const fetchPosts = async (query?: string, language?: string) => {
        loading.value = true;
        try {
            const params: any = {};
            if (query) params.q = query;
            if (language) params.language = language;
            const response = await api.get('/posts', { params });
            posts.value = response.data.data;
        } finally {
            loading.value = false;
        }
    };

    const fetchPost = async (id: number) => {
        loading.value = true;
        try {
            const response = await api.get(`/posts/${id}`);
            currentPost.value = response.data.data;
            return response.data.data;
        } finally {
            loading.value = false;
        }
    };

    const createPost = async (postData: { title: string; body: any[] }) => {
        loading.value = true;
        try {
            const response = await api.post('/posts', postData);
            return response.data.data;
        } finally {
            loading.value = false;
        }
    };

    const fetchTrending = async () => {
        loading.value = true;
        try {
            const response = await api.get('/posts/trending');
            posts.value = response.data.data;
        } finally {
            loading.value = false;
        }
    };

    return {
        posts,
        currentPost,
        loading,
        fetchPosts,
        fetchPost,
        createPost,
        fetchTrending,
    };
});

