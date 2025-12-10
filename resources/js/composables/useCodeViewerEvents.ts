import { ref } from 'vue';

type EventHandler<T> = (data: T) => void;

type Events = {
  'line-selected': { snippetId: number | string; lineNumber: number; blockId?: number | string | null };
  'comment-created': { snippetId: number | string; lineNumber: number; comment: any };
  'comment-updated': { commentId: number | string; updates: any };
  'comment-deleted': { commentId: number | string };
  'thread-resolved': { snippetId: number | string; lineNumber: number; blockId?: number | string | null };
  'thread-unresolved': { snippetId: number | string; lineNumber: number; blockId?: number | string | null };
};

// Simple event bus implementation
class EventBus {
  private handlers: Map<keyof Events, Set<EventHandler<any>>> = new Map();

  on<K extends keyof Events>(event: K, handler: EventHandler<Events[K]>) {
    if (!this.handlers.has(event)) {
      this.handlers.set(event, new Set());
    }
    this.handlers.get(event)!.add(handler);
  }

  off<K extends keyof Events>(event: K, handler: EventHandler<Events[K]>) {
    this.handlers.get(event)?.delete(handler);
  }

  emit<K extends keyof Events>(event: K, data: Events[K]) {
    this.handlers.get(event)?.forEach(handler => {
      try {
        handler(data);
      } catch (error) {
        console.error(`Error in event handler for ${event}:`, error);
      }
    });
  }
}

// Create a singleton event bus
let eventBus: EventBus | null = null;

export function useCodeViewerEvents() {
  if (!eventBus) {
    eventBus = mitt<Events>();
  }

  const selectedLine = ref<{ snippetId: number | string; lineNumber: number; blockId?: number | string | null } | null>(null);
  const activeThread = ref<{ snippetId: number | string; lineNumber: number; blockId?: number | string | null } | null>(null);

  function onLineSelected(handler: (data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) => void) {
    eventBus?.on('line-selected', handler);
    return () => {
      eventBus?.off('line-selected', handler);
    };
  }

  function emitLineSelected(data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) {
    selectedLine.value = data;
    eventBus?.emit('line-selected', data);
  }

  function onCommentCreated(handler: (data: { snippetId: number | string; lineNumber: number; comment: any }) => void) {
    eventBus?.on('comment-created', handler);
    return () => {
      eventBus?.off('comment-created', handler);
    };
  }

  function emitCommentCreated(data: { snippetId: number | string; lineNumber: number; comment: any }) {
    eventBus?.emit('comment-created', data);
  }

  function onCommentUpdated(handler: (data: { commentId: number | string; updates: any }) => void) {
    eventBus?.on('comment-updated', handler);
    return () => {
      eventBus?.off('comment-updated', handler);
    };
  }

  function emitCommentUpdated(data: { commentId: number | string; updates: any }) {
    eventBus?.emit('comment-updated', data);
  }

  function onCommentDeleted(handler: (data: { commentId: number | string }) => void) {
    eventBus?.on('comment-deleted', handler);
    return () => {
      eventBus?.off('comment-deleted', handler);
    };
  }

  function emitCommentDeleted(data: { commentId: number | string }) {
    eventBus?.emit('comment-deleted', data);
  }

  function onThreadResolved(handler: (data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) => void) {
    eventBus?.on('thread-resolved', handler);
    return () => {
      eventBus?.off('thread-resolved', handler);
    };
  }

  function emitThreadResolved(data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) {
    activeThread.value = null;
    eventBus?.emit('thread-resolved', data);
  }

  function onThreadUnresolved(handler: (data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) => void) {
    eventBus?.on('thread-unresolved', handler);
    return () => {
      eventBus?.off('thread-unresolved', handler);
    };
  }

  function emitThreadUnresolved(data: { snippetId: number | string; lineNumber: number; blockId?: number | string | null }) {
    eventBus?.emit('thread-unresolved', data);
  }

  return {
    selectedLine,
    activeThread,
    onLineSelected,
    emitLineSelected,
    onCommentCreated,
    emitCommentCreated,
    onCommentUpdated,
    emitCommentUpdated,
    onCommentDeleted,
    emitCommentDeleted,
    onThreadResolved,
    emitThreadResolved,
    onThreadUnresolved,
    emitThreadUnresolved,
  };
}

// Export a function to create API helpers for comments
export function useCommentApi() {
  async function createComment(data: {
    snippet_id: number | string;
    line_number: number;
    body: string;
    parent_id?: number | string | null;
    block_id?: number | string | null;
  }) {
    // TODO: Replace with actual API call
    // Using Inertia or axios based on project pattern
    const response = await fetch('/api/v1/comments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
    
    if (!response.ok) {
      throw new Error('Failed to create comment');
    }
    
    return await response.json();
  }

  async function updateComment(commentId: number | string, data: { body: string }) {
    const response = await fetch(`/api/v1/comments/${commentId}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    });
    
    if (!response.ok) {
      throw new Error('Failed to update comment');
    }
    
    return await response.json();
  }

  async function deleteComment(commentId: number | string) {
    const response = await fetch(`/api/v1/comments/${commentId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    
    if (!response.ok) {
      throw new Error('Failed to delete comment');
    }
  }

  async function resolveComment(commentId: number | string, resolved: boolean) {
    const response = await fetch(`/api/v1/comments/${commentId}/resolve`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ resolved }),
    });
    
    if (!response.ok) {
      throw new Error('Failed to resolve comment');
    }
    
    return await response.json();
  }

  return {
    createComment,
    updateComment,
    deleteComment,
    resolveComment,
  };
}

