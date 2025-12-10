# PR Code UI Scaffold Documentation

This document describes the Vue 3 + Inertia.js UI scaffold for PR Code, a social platform for sharing code with inline comments.

## 📁 File Structure

```
resources/js/
├── Pages/
│   ├── Feed.vue              # Feed page listing all posts
│   ├── PostShow.vue         # Single post view with inline comments
│   └── CreatePost.vue       # Post creation form
├── Components/
│   ├── PostCard.vue         # Post preview card for feed
│   ├── CodeBlock/
│   │   ├── CodeViewer.vue              # Code display with line numbers
│   │   ├── InlineCommentsLayer.vue     # Manages inline comment UI
│   │   └── InlineCommentPopover.vue     # Popover for adding inline comments
│   ├── Comments/
│   │   ├── CommentList.vue  # General comments list
│   │   └── CommentItem.vue   # Single comment with replies
│   └── Controls/
│       ├── LikeButton.vue    # Like/unlike button with optimistic updates
│       ├── ShareButton.vue   # Share menu (copy link, Twitter, native share)
│       └── FollowButton.vue  # Follow/unfollow user button
├── Composables/
│   └── usePostState.js      # Pinia store for post state management
└── util/
    └── syntax.ts            # PrismJS configuration and helpers
```

## 🎯 Component Props & Usage

### Pages

#### Feed.vue
**Props:**
```javascript
{
  posts: Array<Post>,  // Required
  links: Object        // Optional - pagination links
}
```

**Post Shape:**
```javascript
{
  id: number,
  author: {
    id: number,
    name: string,
    avatar_url: string,
    handle: string
  },
  title: string,
  body: string,  // HTML content
  code: {
    language: string,
    content: string
  },
  stats: {
    likes: number,
    comments: number,
    views: number
  },
  created_at: string  // ISO date string
}
```

#### PostShow.vue
**Props:**
```javascript
{
  post: {
    id: number,
    author: { id, name, avatar_url, handle, is_following? },
    title: string,
    body_html: string,
    code: { language: string, content: string },
    inline_comments: Array<InlineComment>,
    comments: Array<Comment>,
    stats: { likes, comments, views },
    is_liked: boolean,
    created_at: string
  },
  currentUserId: number|string  // Optional
}
```

**InlineComment Shape:**
```javascript
{
  id: number,
  user: { id, name, avatar_url },
  line_number: number,
  text: string,
  created_at: string
}
```

**Comment Shape:**
```javascript
{
  id: number,
  user: { id, name, avatar_url },
  text: string,
  created_at: string,
  parent_id: number|null,
  replies?: Array<Comment>
}
```

#### CreatePost.vue
**Props:**
```javascript
{
  errors: Object  // Validation errors from Laravel
}
```

**Form Fields:**
- `title` (required)
- `body` (required)
- `code_language` (optional)
- `code_content` (optional)

### Components

#### CodeViewer.vue
**Props:**
```javascript
{
  code: { language: string, content: string },
  copyable: boolean,      // Default: true
  selectableLines: boolean  // Default: true
}
```

**Events:**
- `line-click` - Emitted when a line is clicked
- `line-select` - Emitted when lines are selected (array of line numbers)

#### InlineCommentsLayer.vue
**Props:**
```javascript
{
  code: { language: string, content: string },
  inlineComments: Array<InlineComment>,
  postId: number|string,
  copyable: boolean
}
```

**Events:**
- `comment-added` - Emitted when a new inline comment is added

#### CommentList.vue
**Props:**
```javascript
{
  comments: Array<Comment>,
  postId: number|string,
  currentUserId: number|string,  // Optional
  loading: boolean
}
```

**Events:**
- `comment-added` - Emitted when a new comment is added
- `comment-deleted` - Emitted when a comment is deleted

#### LikeButton.vue
**Props:**
```javascript
{
  postId: number|string,
  initialLiked: boolean,
  initialCount: number
}
```

**Events:**
- `liked` - Emitted when post is liked
- `unliked` - Emitted when post is unliked

#### ShareButton.vue
**Props:**
```javascript
{
  postId: number|string,
  postTitle: string,     // Optional
  postUrl: string        // Optional - defaults to current post URL
}
```

**Events:**
- `shared` - Emitted when share action completes

#### FollowButton.vue
**Props:**
```javascript
{
  userId: number|string,
  initialFollowing: boolean
}
```

**Events:**
- `followed` - Emitted when user is followed
- `unfollowed` - Emitted when user is unfollowed

## 🔌 Backend Integration

### Expected Routes

The components expect these Laravel routes:

```php
// Posts
GET  /feed                    -> Feed page
GET  /posts                   -> Feed page (alternative)
GET  /posts/create            -> CreatePost page
POST /posts                   -> Store new post
GET  /posts/{id}              -> PostShow page
POST /posts/{id}/like         -> Toggle like

// Comments
POST /posts/{id}/comments     -> Add general comment
POST /posts/{id}/inline-comments  -> Add inline comment
DELETE /comments/{id}         -> Delete comment

// Users
POST /users/{id}/follow       -> Follow user
POST /users/{id}/unfollow     -> Unfollow user
```

### Controller Examples

See `routes/web.php` for detailed controller method examples.

## 🎨 Styling

All components use TailwindCSS with dark mode support. The code blocks use PrismJS with the "tomorrow" theme (dark).

**Customization:**
- Modify Tailwind classes in components
- Swap Prism theme in `util/syntax.ts`
- Adjust color scheme via Tailwind config

## ♿ Accessibility

- Keyboard navigation for line numbers (Enter/Space to select)
- ARIA labels on interactive elements
- Focus management in popovers
- Screen reader friendly comment structure

## 🚀 Features

### Optimistic Updates
- Likes update immediately via Pinia store
- Comments show pending state
- Automatic rollback on error

### Responsive Design
- Desktop: Side-by-side code + comments
- Mobile: Collapsible comment panels below code
- Touch-friendly interactions

### Code Highlighting
- PrismJS with multiple language support
- Line numbers with click targets
- Copy to clipboard functionality

## 📝 Notes

1. **PrismJS Setup**: Import Prism CSS in your main CSS file or via `util/syntax.ts`
2. **Pinia Store**: Initialize Pinia in your `app.js`:
   ```javascript
   import { createPinia } from 'pinia';
   const pinia = createPinia();
   app.use(pinia);
   ```
3. **Code Editor**: CreatePost uses a simple textarea. Consider upgrading to CodeMirror or Monaco Editor for better UX.
4. **Image Handling**: Default avatar fallback is `/default-avatar.png` - ensure this exists or update components.

## 🔧 Setup Checklist

- [ ] Install dependencies: `npm install prismjs pinia`
- [ ] Import Prism CSS in your main CSS file
- [ ] Initialize Pinia in `app.js`
- [ ] Create Laravel controllers matching expected props
- [ ] Set up routes as documented
- [ ] Add default avatar image
- [ ] Configure Tailwind dark mode if not already enabled
- [ ] Test optimistic updates with network throttling

## 📚 Additional Resources

- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [PrismJS Documentation](https://prismjs.com/)
- [Pinia Documentation](https://pinia.vuejs.org/)


