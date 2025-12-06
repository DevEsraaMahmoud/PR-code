# PR Code UI - Quick Start Guide

## ✅ Files Created

All UI scaffold files have been created in the following structure:

```
resources/js/
├── Pages/
│   ├── Feed.vue              ✅ Created
│   ├── PostShow.vue          ✅ Created
│   └── CreatePost.vue        ✅ Created
├── Components/
│   ├── PostCard.vue          ✅ Created
│   ├── CodeBlock/
│   │   ├── CodeViewer.vue              ✅ Created
│   │   ├── InlineCommentsLayer.vue     ✅ Created
│   │   └── InlineCommentPopover.vue     ✅ Created
│   ├── Comments/
│   │   ├── CommentList.vue  ✅ Created
│   │   └── CommentItem.vue   ✅ Created
│   └── Controls/
│       ├── LikeButton.vue    ✅ Created
│       ├── ShareButton.vue   ✅ Created
│       └── FollowButton.vue  ✅ Created
├── Composables/
│   └── usePostState.js      ✅ Created (Pinia store)
└── util/
    └── syntax.ts            ✅ Created (PrismJS config)
```

## 🚀 Next Steps

### 1. Install Dependencies (if not already installed)
```bash
npm install prismjs pinia
```

### 2. Initialize Pinia in `resources/js/app.js`
```javascript
import { createPinia } from 'pinia';
const pinia = createPinia();
app.use(pinia);
```

### 3. Import Prism CSS
Add to `resources/css/app.css`:
```css
@import 'prismjs/themes/prism-tomorrow.css';
```

Or import in `resources/js/util/syntax.ts` (already done).

### 4. Update Laravel Controllers

Your controllers should return Inertia responses with the expected prop shapes. See `routes/web.php` comments for examples.

**Example Feed Controller:**
```php
public function index()
{
    return Inertia::render('Feed', [
        'posts' => Post::with(['author', 'code'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(15)
            ->through(fn($post) => [
                'id' => $post->id,
                'author' => [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                    'avatar_url' => $post->author->avatar_url,
                    'handle' => $post->author->handle,
                ],
                'title' => $post->title,
                'body' => $post->body_html,
                'code' => [
                    'language' => $post->code->language ?? 'text',
                    'content' => $post->code->content ?? '',
                ],
                'stats' => [
                    'likes' => $post->likes_count,
                    'comments' => $post->comments_count,
                    'views' => $post->views ?? 0,
                ],
                'created_at' => $post->created_at->toISOString(),
            ]),
    ]);
}
```

### 5. Add Routes (if not already present)

Ensure these routes exist in `routes/web.php`:
- `GET /feed` → Feed page
- `GET /posts/{id}` → PostShow page
- `GET /posts/create` → CreatePost page
- `POST /posts` → Store post
- `POST /posts/{id}/like` → Toggle like
- `POST /posts/{id}/comments` → Add comment
- `POST /posts/{id}/inline-comments` → Add inline comment
- `POST /users/{id}/follow` → Follow user
- `POST /users/{id}/unfollow` → Unfollow user

### 6. Test the UI

1. Start your dev server: `npm run dev`
2. Visit `/feed` to see the feed page
3. Click a post to view `PostShow` page
4. Click "New Post" to create a post

## 📋 Component Usage Examples

### Using PostCard in Feed
```vue
<PostCard :post="post" />
```

### Using CodeViewer standalone
```vue
<CodeViewer 
  :code="{ language: 'javascript', content: 'console.log("Hello");' }"
  :copyable="true"
  @line-click="handleLineClick"
/>
```

### Using LikeButton
```vue
<LikeButton 
  :post-id="123"
  :initial-liked="false"
  :initial-count="10"
  @liked="handleLiked"
/>
```

## 🎨 Customization

- **Colors**: Modify Tailwind classes in components
- **Code Theme**: Change Prism theme in `util/syntax.ts`
- **Fonts**: Update font-family in CodeViewer component

## 📚 Documentation

See `PR_CODE_UI_SCAFFOLD.md` for complete documentation including:
- All component props and events
- Expected data shapes
- Backend integration examples
- Accessibility features

## ⚠️ Notes

1. **Case Sensitivity**: Windows file system is case-insensitive, but ensure your imports match the actual file casing
2. **Existing Files**: Some components may already exist (like `CodeViewer.vue`). The new scaffold uses `CodeBlock/CodeViewer.vue`
3. **Default Avatar**: Components expect `/default-avatar.png` - create this or update the fallback paths
4. **Code Editor**: CreatePost uses a simple textarea. Consider upgrading to CodeMirror/Monaco for production

## 🐛 Troubleshooting

**Import errors**: Check that file paths match your actual directory structure
**Prism not highlighting**: Ensure Prism CSS is imported and languages are registered
**Pinia errors**: Verify Pinia is initialized in `app.js`
**Styling issues**: Ensure Tailwind is configured and dark mode is enabled if needed

