import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../composables/useApi';

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

export const useCommentsStore = defineStore('comments', () => {
    const comments = ref<Comment[]>([]);
    const loading = ref(false);

    const fetchComments = async (snippetId: number) => {
        loading.value = true;
        try {
            const response = await api.get('/comments', { params: { snippet_id: snippetId } });
            comments.value = response.data.data;
        } finally {
            loading.value = false;
        }
    };

    const createComment = async (commentData: {
        snippet_id: number;
        parent_id?: number;
        start_line: number;
        end_line: number;
        body: string;
    }) => {
        loading.value = true;
        try {
            const response = await api.post('/comments', commentData);
            // Refresh comments after creation
            await fetchComments(commentData.snippet_id);
            return response.data.data;
        } finally {
            loading.value = false;
        }
    };

    return {
        comments,
        loading,
        fetchComments,
        createComment,
    };
});

