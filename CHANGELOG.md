# Changelog - PR Code Post with Inline Comments

## Implementation Complete

### Backend Changes

#### New Files
- `database/seeders/PostSeeder.php` - Sample data seeder
- `tests/Feature/InlineCommentTest.php` - Feature tests for inline comments

#### Modified Files
- `app/Http/Resources/PostResource.php` - Added inline_comments_index structure
- `app/Http/Resources/CommentResource.php` - Added is_inline, edited_at, likes_count
- `app/Http/Resources/SnippetResource.php` - Added allComments relationship
- `app/Http/Controllers/Api/CommentController.php` - Added storeInlineComment() and updateInlineComment()
- `app/Http/Controllers/Web/PostController.php` - Updated to use PostResource
- `app/Services/PostService.php` - Enhanced to load all relationships
- `app/Repositories/PostRepository.php` - Updated eager loading
- `routes/api.php` - Added inline comment routes
- `database/seeders/DatabaseSeeder.php` - Added PostSeeder call
- `tests/Feature/PostTest.php` - Updated to test inline_comments_index

### Frontend Changes

#### New Files
- `resources/js/components/InlineCommentBubble.vue` - Gutter comment markers
- `resources/js/components/InlineCommentsPanel.vue` - Side panel/bottom sheet
- `resources/js/components/GeneralComments.vue` - General comments component
- `resources/js/components/PostBlock.vue` - Top-level post block component
- `resources/js/utils/shiki-init.ts` - Shiki initialization utility

#### Modified Files
- `resources/js/components/CodeViewer.vue` - Complete refactor with Shiki
- `resources/js/Pages/Posts/Show.vue` - Refactored to use new components

#### Dependencies
- Added `shiki` package for syntax highlighting

## Features Implemented

✅ **Syntax Highlighting**
- Shiki integration with VSCode-like dark theme
- Support for multiple languages (PHP, JavaScript, TypeScript, Python, etc.)
- Fallback to plain text if highlighting fails

✅ **Inline Comments**
- Comments attached to specific code lines
- Comment bubbles in gutter showing count
- Clickable lines to view/add comments
- Side-by-side panel (desktop) / Bottom sheet (mobile)

✅ **General Comments**
- Threaded comments below post
- Reply support (1-level)
- Edit/Delete functionality

✅ **Responsive Design**
- Desktop: 2/3 content + 1/3 sidebar
- Mobile: Full-width content + bottom sheet
- Smooth transitions and animations

✅ **Accessibility**
- Keyboard navigation (tab, enter)
- ARIA labels
- Semantic HTML

✅ **API Endpoints**
- GET /api/posts/{id} - Returns post with inline_comments_index
- POST /api/posts/{id}/inline-comments - Create inline comment
- PATCH /api/inline-comments/{id} - Update inline comment
- POST /api/comments - Create general comment/reply
- DELETE /api/comments/{id} - Delete comment

✅ **Testing**
- Feature tests for inline comment endpoints
- Updated post tests to verify inline_comments_index structure

✅ **Sample Data**
- Seeder with PHP code snippet
- 3 inline comments on lines 4, 9, 15
- 2 general comments with 1 reply

## Component Architecture

```
Show.vue (Page)
├── PostBlock.vue
│   ├── Text blocks
│   └── CodeViewer.vue
│       ├── Line numbers gutter
│       └── InlineCommentBubble.vue (per line)
├── GeneralComments.vue
└── InlineCommentsPanel.vue (desktop sidebar / mobile bottom sheet)
```

## Data Structure

### inline_comments_index
```json
{
  "4": [comment1, comment2],
  "9": [comment3],
  "15": [comment4]
}
```

Maps line numbers to arrays of comments, allowing fast lookups when rendering code lines.

## Styling

- Code font: `text-xs` (0.75rem) for compact display
- Line height: 24px (1.5rem)
- Theme: VSCode dark (`#0d1117` background)
- Responsive breakpoint: 1024px (lg)

## Next Steps (Optional)

- [ ] Vue component tests (Vitest)
- [ ] Multi-line range selection for inline comments
- [ ] Real-time updates with WebSockets
- [ ] Markdown support in comments
- [ ] Code suggestions/diffs
- [ ] Resolve/unresolve comments


