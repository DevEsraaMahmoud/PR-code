# PR Code Social - Component Usage Guide

This document provides usage examples and API documentation for the modernized Vue components.

## CodeViewer.vue

A full-featured code viewer with syntax highlighting, line numbers, and inline comment support.

### Props

```typescript
{
  snippet: {
    id: number | string;
    language: string;
    content: string;
    versions?: any[];
  };
  highlightedLines?: number[];
  inlineCommentsIndex?: Record<string, any[]>; // { lineNumber: comments[] }
  isAuthenticated?: boolean;
  canCopy?: boolean;
  canOpenDiff?: boolean;
}
```

### Events

- `lineSelected({ lineNumber })` - Emitted when a line is clicked
- `copySucceeded()` - Emitted when code is successfully copied
- `openDiffRequested(snippetId)` - Emitted when diff view is requested

### Example Usage

```vue
<template>
  <CodeViewer
    :snippet="{
      id: 1,
      language: 'javascript',
      content: 'const hello = "world";'
    }"
    :inline-comments-index="commentsIndex"
    :is-authenticated="true"
    :can-copy="true"
    @line-selected="handleLineSelected"
    @copy-succeeded="handleCopySuccess"
  />
</template>

<script setup>
import CodeViewer from '@/Components/CodeViewer.vue';

const commentsIndex = {
  '1': [{ id: 1, body: 'Comment on line 1', user: { name: 'John' } }],
};

function handleLineSelected({ lineNumber }) {
  console.log('Line selected:', lineNumber);
}
</script>
```

### Features

- **Keyboard Navigation**: Arrow keys to navigate, Enter to select
- **Copy Functionality**: Click copy button or use keyboard shortcuts
- **Comment Highlighting**: Lines with comments show yellow accent border
- **Accessibility**: Full ARIA support and keyboard navigation

---

## InlineCommentPanel.vue

Right-side collapsible panel for displaying inline comment threads.

### Props

```typescript
{
  isOpen?: boolean;
  threads?: Array<{
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    commentCount?: number;
    messages?: any[];
    resolved?: boolean;
    excerpt?: string;
    author?: string;
    reactionsCount?: number;
  }>;
  activeThread?: {
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    messages: any[];
    resolved: boolean;
  } | null;
  currentUserId?: number;
  canResolve?: boolean;
}
```

### Events

- `toggle()` - Toggle panel open/closed
- `thread-selected(thread)` - Thread was selected
- `thread-updated()` - Thread was updated
- `thread-resolved({ lineNumber, blockId, resolved })` - Thread was resolved
- `thread-unresolved({ lineNumber, blockId })` - Thread was unresolved

### Example Usage

```vue
<template>
  <div class="flex">
    <div class="flex-1">
      <!-- Code viewer -->
    </div>
    <InlineCommentPanel
      :is-open="panelOpen"
      :threads="threads"
      :active-thread="activeThread"
      :current-user-id="currentUserId"
      @toggle="panelOpen = !panelOpen"
      @thread-selected="handleThreadSelected"
      @thread-resolved="handleThreadResolved"
    />
  </div>
</template>
```

---

## CommentThread.vue

Component for displaying and interacting with a single comment thread.

### Props

```typescript
{
  thread: {
    lineNumber: number;
    blockId: number | string;
    postId: number | string;
    messages: any[];
    resolved: boolean;
  };
  currentUserId?: number;
  canResolve?: boolean;
}
```

### Events

- `reply(commentId, body)` - Reply to a comment
- `resolve()` - Resolve the thread
- `unresolve()` - Unresolve the thread
- `edit(comment)` - Edit a comment
- `delete(commentId)` - Delete a comment
- `react(commentId, reactionType)` - Add reaction

### Example Usage

```vue
<template>
  <CommentThread
    :thread="thread"
    :current-user-id="userId"
    :can-resolve="true"
    @reply="handleReply"
    @resolve="handleResolve"
    @edit="handleEdit"
  />
</template>

<script setup>
async function handleReply(commentId, body) {
  // Call API to create reply
  await fetch(`/api/v1/comments`, {
    method: 'POST',
    body: JSON.stringify({
      parent_id: commentId,
      body,
    }),
  });
}
</script>
```

### Features

- **Optimistic UI**: Updates immediately, rolls back on error
- **Nested Replies**: Supports unlimited nesting
- **Edit/Delete**: For comment owners
- **Reactions**: Integrated reactions component

---

## Reactions.vue

Generic reactions component for posts and comments.

### Props

```typescript
{
  targetId: number | string;
  targetType: 'post' | 'comment';
  initialReactions?: Array<{
    id: number;
    type: string;
    user_id: number;
  }>;
  currentUserId?: number;
}
```

### Events

- `reaction-changed({ targetId, reactions, counts })` - Reaction was added/removed

### Example Usage

```vue
<template>
  <Reactions
    :target-id="post.id"
    target-type="post"
    :initial-reactions="post.reactions"
    :current-user-id="userId"
    @reaction-changed="handleReactionChanged"
  />
</template>
```

### Reaction Types

- `like` - 👍
- `love` - ❤️
- `wow` - 😮
- `eyes` - 👀

---

## FeedList.vue

Feed list component with sorting and infinite scroll.

### Props

```typescript
{
  posts: any[];
  currentSort?: 'recent' | 'top' | 'trending';
  showSorting?: boolean;
  hasMore?: boolean;
  totalCount?: number;
  loading?: boolean;
}
```

### Events

- `sort-changed(sort)` - Sort option changed
- `load-more()` - Load more posts requested

### Example Usage

```vue
<template>
  <FeedList
    :posts="posts"
    :current-sort="sort"
    :has-more="hasMore"
    :loading="loading"
    @sort-changed="handleSortChange"
    @load-more="handleLoadMore"
  />
</template>
```

---

## PostEditor.vue

WYSIWYG/Markdown editor for creating and editing posts.

### Props

```typescript
{
  postId?: number | string;
  initialData?: {
    title?: string;
    body?: string;
    code?: {
      language: string;
      content: string;
    };
  };
}
```

### Events

- `cancel()` - Cancel editing
- `saved(postId)` - Post was saved

### Example Usage

```vue
<template>
  <PostEditor
    :initial-data="postData"
    @saved="handleSaved"
    @cancel="handleCancel"
  />
</template>
```

### Features

- **Auto-save**: Drafts saved to localStorage every 2 seconds
- **File Upload**: Upload code files
- **Code Preview**: Preview code with syntax highlighting
- **Markdown Support**: Text editor supports markdown

---

## NotificationsDropdown.vue

Dropdown component for displaying notifications.

### Props

```typescript
{
  notifications?: Array<{
    id: number | string;
    type: string;
    title?: string;
    message?: string;
    body?: string;
    read_at?: string | null;
    created_at: string;
    data?: any;
  }>;
  maxItems?: number;
}
```

### Events

- `notification-clicked(notification)` - Notification was clicked
- `mark-read(notificationId)` - Mark notification as read
- `mark-all-read()` - Mark all notifications as read

### Example Usage

```vue
<template>
  <NotificationsDropdown
    :notifications="notifications"
    :max-items="10"
    @notification-clicked="handleNotificationClick"
    @mark-read="handleMarkRead"
  />
</template>
```

---

## API Endpoints

### Comments API

#### Create Comment
```javascript
POST /api/v1/comments
{
  snippet_id: number,
  line_number: number,
  body: string,
  parent_id?: number, // For replies
  block_id?: number
}
```

#### Update Comment
```javascript
PATCH /api/v1/comments/{id}
{
  body: string
}
```

#### Delete Comment
```javascript
DELETE /api/v1/comments/{id}
```

#### Resolve/Unresolve Comment
```javascript
PATCH /api/v1/comments/{id}/resolve
{
  resolved: boolean
}
```

### Reactions API

#### Add Reaction
```javascript
POST /api/v1/reactions
{
  target_id: number,
  target_type: 'post' | 'comment',
  type: 'like' | 'love' | 'wow' | 'eyes'
}
```

#### Remove Reaction
```javascript
DELETE /api/v1/reactions/{id}
```

### Posts API

#### Create Post
```javascript
POST /api/v1/posts
{
  title: string,
  body: string,
  code?: {
    language: string,
    content: string
  }
}
```

---

## Event Bus (useCodeViewerEvents)

Composable for handling events between CodeViewer and InlineCommentPanel.

### Example Usage

```vue
<script setup>
import { useCodeViewerEvents } from '@/composables/useCodeViewerEvents';

const { onLineSelected, emitLineSelected } = useCodeViewerEvents();

onLineSelected(({ snippetId, lineNumber, blockId }) => {
  console.log('Line selected:', lineNumber);
  // Open comment panel, load thread, etc.
});
</script>
```

---

## Testing

Unit tests are available in `tests/unit/`:

- `CodeViewer.spec.js` - Tests for CodeViewer component
- `CommentThread.spec.js` - Tests for CommentThread component

Run tests with:
```bash
npm run test
```

---

## Real-time Updates (TODO)

For real-time updates using Laravel Echo:

```javascript
// TODO: Add Echo listeners
Echo.channel(`post.${postId}`)
  .listen('CommentCreated', (e) => {
    // Update inline comments index
    // Add comment to thread
  })
  .listen('CommentUpdated', (e) => {
    // Update comment in thread
  })
  .listen('ThreadResolved', (e) => {
    // Update thread resolved status
  });
```

---

## Accessibility

All components include:
- ARIA labels and roles
- Keyboard navigation support
- Focus management
- Screen reader friendly

---

## Mobile Support

- Line clicks on mobile open modal composer
- InlineCommentPanel collapses on mobile
- Touch-friendly interactions
- Responsive layouts


