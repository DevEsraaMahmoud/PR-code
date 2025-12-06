# PR Code Post with Inline Comments - Implementation Summary

## Overview
This implementation adds a comprehensive PR code review feature with inline comments, syntax highlighting using Shiki, and a responsive UI that works on both desktop and mobile devices.

## Files Created/Modified

### Backend (Laravel)

#### API Resources
- **app/Http/Resources/PostResource.php** - Updated to include inline_comments_index structure
- **app/Http/Resources/CommentResource.php** - Updated to include is_inline, edited_at, and likes_count
- **app/Http/Resources/SnippetResource.php** - Updated to include allComments relationship

#### Controllers
- **app/Http/Controllers/Api/CommentController.php** - Added `storeInlineComment()` and `updateInlineComment()` methods
- **app/Http/Controllers/Web/PostController.php** - Updated to use PostResource

#### Services & Repositories
- **app/Services/PostService.php** - Updated to ensure all relationships are loaded
- **app/Repositories/PostRepository.php** - Updated to load comments and snippets with proper relationships

#### Routes
- **routes/api.php** - Added routes for inline comments:
  - `POST /api/posts/{id}/inline-comments`
  - `PATCH /api/inline-comments/{id}`

#### Seeders
- **database/seeders/PostSeeder.php** - Created seeder with sample post, PHP code snippet, inline comments, and general comments
- **database/seeders/DatabaseSeeder.php** - Updated to call PostSeeder

#### Tests
- **tests/Feature/InlineCommentTest.php** - Feature tests for inline comment endpoints
- **tests/Feature/PostTest.php** - Updated to test inline_comments_index in GET response

### Frontend (Vue 3 + Inertia.js)

#### Components
- **resources/js/components/CodeViewer.vue** - Completely refactored with Shiki highlighting:
  - Line numbers with clickable gutter
  - Inline comment bubbles on lines with comments
  - Emits `line-selected` event with line number and comments
  - Small font size (text-xs) for compact display
  - VSCode-like dark theme

- **resources/js/components/InlineCommentBubble.vue** - New component:
  - Small clickable dot/bubble in gutter showing comment count
  - Accessible with aria-labels

- **resources/js/components/InlineCommentsPanel.vue** - New component:
  - Desktop: Side panel (sticky)
  - Mobile: Bottom sheet with backdrop
  - Shows comments for selected line
  - Form to add new inline comments
  - Optimistic UI updates
  - Reply support (1-level)

- **resources/js/components/GeneralComments.vue** - New component:
  - List of general (non-inline) comments
  - Threaded replies (1-level)
  - Add comment form
  - Edit/Delete actions

- **resources/js/components/PostBlock.vue** - New component:
  - Combines text blocks and code blocks
  - Handles inline comments index per snippet
  - Emits line-selected events

#### Pages
- **resources/js/Pages/Posts/Show.vue** - Completely refactored:
  - Uses new component structure
  - Grid layout: 2/3 content, 1/3 sidebar (desktop)
  - Mobile: Full-width content, bottom sheet for inline comments
  - Smooth transitions for panel open/close

#### Utilities
- **resources/js/utils/shiki-init.ts** - Shiki initialization utility:
  - Lazy loading of highlighter
  - Language mapping
  - Error handling with fallback

### Dependencies
- **package.json** - Added `shiki` package

## API Endpoints

### GET /api/posts/{id}
Returns post with:
- `snippets` - Array of code snippets
- `comments` - Array of general comments
- `inline_comments_index` - Object mapping line numbers to comment arrays: `{ 4: [comment1, comment2], 9: [comment3] }`

### POST /api/posts/{id}/inline-comments
Creates an inline comment. Body:
```json
{
  "snippet_id": 1,
  "start_line": 4,
  "end_line": 4,
  "body": "Comment text",
  "is_inline": true
}
```

### PATCH /api/inline-comments/{id}
Updates an inline comment. Body:
```json
{
  "body": "Updated comment text"
}
```

### POST /api/comments
Creates a general comment or reply. Body:
```json
{
  "post_id": 1,
  "parent_id": null, // or comment ID for replies
  "body": "Comment text",
  "is_inline": false
}
```

## Component Props & Events

### CodeViewer
**Props:**
- `code: string` - Code text
- `language: string` - Programming language
- `inlineCommentsIndex?: Record<number, any[]>` - Comments indexed by line number
- `isAuthenticated?: boolean`

**Events:**
- `line-selected: { line: number; comments: any[] }`

### InlineCommentsPanel
**Props:**
- `selectedLine: number | null`
- `comments: any[]`
- `postId: number`
- `isAuthenticated?: boolean`
- `currentUserId?: number`

**Events:**
- `close`
- `edit: (comment: any)`
- `comment-added`

### PostBlock
**Props:**
- `post: any`
- `isAuthenticated?: boolean`

**Events:**
- `line-selected: { line: number; comments: any[]; snippetId: number }`

## Styling

- **Code Viewer**: VSCode-like dark theme (`#0d1117` background)
- **Font Size**: `text-xs` (0.75rem) for code
- **Line Height**: 24px (1.5rem)
- **Responsive**: Mobile-first, panel collapses to bottom sheet on < 1024px

## How to Run

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

3. **Seed database:**
   ```bash
   php artisan db:seed
   ```

4. **Start development servers:**
   ```bash
   # Terminal 1: Laravel
   php artisan serve
   
   # Terminal 2: Vite
   npm run dev
   ```

5. **Visit the sample post:**
   - Navigate to `/posts/pr-add-payment-webhook-handler`
   - Or find the post ID from the database and visit `/posts/{id}`

## Testing

Run feature tests:
```bash
php artisan test --filter InlineCommentTest
php artisan test --filter PostTest
```

## Key Features Implemented

✅ Syntax highlighting with Shiki (VSCode-like theme)
✅ Inline comments attached to specific code lines
✅ Side-by-side inline comments panel (desktop)
✅ Bottom sheet for mobile devices
✅ General comments below post
✅ Collapsible/expand behavior
✅ Small-font code view (text-xs)
✅ Line number gutter with clickable lines
✅ Comment bubbles showing comment count
✅ Optimistic UI updates
✅ Reply support (1-level)
✅ Author + timestamp for comments
✅ Edit/Delete functionality
✅ Keyboard accessibility
✅ ARIA labels for accessibility

## Sample Data

The seeder creates:
- 1 post: "PR: add payment webhook handler"
- 1 PHP code snippet (~20 lines)
- 3 inline comments on lines 4, 9, and 15
- 2 general comments
- 1 reply to a general comment

## Next Steps (Optional Enhancements)

- [ ] Unit tests for Vue components (Vitest)
- [ ] Support for multi-line inline comments (range selection)
- [ ] Real-time updates with WebSockets
- [ ] Markdown support in comments
- [ ] Code suggestions/diffs
- [ ] Resolve/unresolve comments
- [ ] Email notifications

