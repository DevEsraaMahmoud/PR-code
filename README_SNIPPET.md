# Quick Start Guide

## Setup

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```

4. **Seed sample data:**
   ```bash
   php artisan db:seed
   ```
   This creates a sample post "PR: add payment webhook handler" with PHP code and inline comments.

## Running the Application

Start both Laravel and Vite development servers:

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

Visit `http://localhost:8000` and navigate to `/posts/pr-add-payment-webhook-handler` to see the sample post.

## Testing

Run feature tests:
```bash
php artisan test
```

Run specific test suite:
```bash
php artisan test --filter InlineCommentTest
```

## Key Features

- ✅ Syntax highlighting with Shiki (VSCode-like theme)
- ✅ Inline comments on code lines
- ✅ Side-by-side comments panel (desktop)
- ✅ Mobile-responsive bottom sheet
- ✅ General comments section
- ✅ Reply support
- ✅ Edit/Delete comments

## API Endpoints

- `GET /api/posts/{id}` - Get post with inline comments index
- `POST /api/posts/{id}/inline-comments` - Create inline comment
- `PATCH /api/inline-comments/{id}` - Update inline comment
- `POST /api/comments` - Create general comment or reply
- `DELETE /api/comments/{id}` - Delete comment

## Component Structure

- `PostBlock.vue` - Top-level component combining text + code blocks
- `CodeViewer.vue` - Code display with syntax highlighting and line numbers
- `InlineCommentBubble.vue` - Gutter markers for comments
- `InlineCommentsPanel.vue` - Side panel/bottom sheet for inline comments
- `GeneralComments.vue` - General comments section below post


