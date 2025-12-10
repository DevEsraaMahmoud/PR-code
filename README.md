# PR Code Social Platform

A LinkedIn/GitHub-like social platform built for developers to share code, collaborate through inline comments, and build a developer community.

## 🚀 Features

### Core Features
- **Code Viewer**: Syntax-highlighted code blocks with line numbers, copy functionality, and line highlighting
- **Inline Comments**: Threaded comments attached to specific line numbers with resolve/unresolve functionality
- **Social Interactions**: Reactions (like, love, wow, clap, lightbulb, laugh), comments, shares, bookmarks
- **Posts & Feed**: Create/edit/delete posts with code snippets, sorting (top, recent, trending), and filtering
- **User Profiles**: Bio, skills, location, follower/following counts, posts list
- **Notifications**: Real-time notifications for comments, mentions, follows, reactions
- **Dark/Light Mode**: Theme toggle persisted per user

### Tech Stack
- **Backend**: Laravel 12+ with Sanctum authentication
- **Frontend**: Vue 3 + Inertia.js + Tailwind CSS
- **Database**: MySQL
- **Code Highlighting**: Prism.js

## 📋 Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Redis (optional, for caching)

## 🔧 Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd pr-code
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Install Node dependencies
```bash
npm install
```

### 4. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pr_code
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run migrations
```bash
php artisan migrate
```

### 6. Seed demo data (optional)
```bash
php artisan db:seed
```

### 7. Build frontend assets
```bash
npm run build
# Or for development:
npm run dev
```

### 8. Start the development server
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 📁 Project Structure

### Backend (Laravel)
```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── PostController.php
│           ├── CommentController.php
│           ├── ReactionController.php
│           ├── FollowController.php
│           ├── BookmarkController.php
│           └── ProfileController.php
├── Models/
│   ├── Post.php
│   ├── Comment.php
│   ├── Snippet.php
│   ├── Reaction.php
│   ├── Follow.php
│   ├── Bookmark.php
│   └── User.php
└── Policies/
    └── CommentPolicy.php

database/
├── migrations/
│   ├── 2025_12_07_000001_create_reactions_table.php
│   ├── 2025_12_07_000002_create_follows_table.php
│   ├── 2025_12_07_000003_create_bookmarks_table.php
│   ├── 2025_12_07_000004_create_snippet_versions_table.php
│   ├── 2025_12_07_000005_add_resolved_to_comments_table.php
│   └── 2025_12_07_000006_add_profile_fields_to_users_table.php
└── seeders/
    └── DatabaseSeeder.php
```

### Frontend (Vue 3)
```
resources/js/
├── components/
│   ├── CodeSnippetCompact.vue (Code viewer with line numbers)
│   ├── PostCard.vue
│   ├── InlineThreadsPanel.vue
│   └── InlineCommentPopover.vue
├── Pages/
│   ├── Home.vue (Feed)
│   └── Posts/
│       ├── Show.vue
│       ├── Create.vue
│       └── Edit.vue
└── Layouts/
    └── AppLayout.vue
```

## 🗄️ Database Schema

### Key Tables
- **users**: User accounts with profile fields
- **posts**: User posts with title, body, visibility
- **snippets**: Code snippets attached to posts
- **comments**: Threaded comments (regular and inline)
- **reactions**: Polymorphic reactions (like, love, wow, etc.)
- **follows**: User follow relationships
- **bookmarks**: User bookmarks (polymorphic)
- **snippet_versions**: Version history for diff mode
- **notifications**: User notifications
- **tags**: Post tags
- **likes**: Legacy like system (can be migrated to reactions)

## 🔌 API Endpoints

### Posts
- `GET /api/posts` - List posts (paginated, filterable)
- `GET /api/posts/trending` - Trending posts
- `GET /api/posts/{id}` - Get single post
- `POST /api/posts` - Create post (auth required)
- `PUT /api/posts/{id}` - Update post (auth required)
- `DELETE /api/posts/{id}` - Delete post (auth required)

### Comments
- `GET /api/posts/{post}/comments` - Get comments for a post
- `POST /api/posts/{post}/comments` - Create comment (auth required)
- `PUT /api/comments/{comment}` - Update comment (auth required)
- `DELETE /api/comments/{comment}` - Delete comment (auth required)
- `POST /api/comments/{comment}/resolve` - Resolve/unresolve comment (auth required)

### Reactions
- `POST /api/{type}/{id}/reactions` - Toggle reaction (post|comment)
- `GET /api/{type}/{id}/reactions` - Get reactions for a post/comment

### Follows
- `POST /api/users/{user}/follow` - Follow a user (auth required)
- `DELETE /api/users/{user}/follow` - Unfollow a user (auth required)
- `GET /api/users/{user}/follow-status` - Check follow status

### Bookmarks
- `GET /api/bookmarks` - Get user's bookmarks (auth required)
- `POST /api/posts/{post}/bookmark` - Bookmark a post (auth required)
- `DELETE /api/posts/{post}/bookmark` - Remove bookmark (auth required)

### Profile
- `GET /api/users/{user}/profile` - Get user profile
- `PUT /api/profile` - Update own profile (auth required)
- `GET /api/users/{user}/posts` - Get user's posts
- `GET /api/users/{user}/bookmarks` - Get user's bookmarks (auth required)

## 🧪 Testing

### Run tests
```bash
php artisan test
```

### Test Coverage
- Post creation with snippets
- Inline comment creation
- Comment resolve/unresolve
- Reaction toggle
- Follow/unfollow
- Bookmark add/remove

## 🎨 Frontend Components

### CodeViewer (`CodeSnippetCompact.vue`)
- Syntax highlighting with Prism.js
- Line numbers with click handlers
- Copy button
- Line highlighting for comments
- Inline comment indicators

### PostCard
- Author info, title, content preview
- Code snippet preview
- Engagement buttons (like, comment, share, save)
- Reaction counts

### InlineCommentPopover
- Shows when clicking a line with comments
- Displays comment thread
- Reply form
- Emoji reactions

### Feed
- Infinite scroll
- Sorting (recent, top, trending)
- Tag filtering
- Post cards

## 🔐 Authentication

The platform uses Laravel Sanctum for SPA authentication.

### Register
```bash
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login
```bash
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

Response includes `token` for authenticated requests.

## 📝 Usage Examples

### Create a Post with Code Snippet
```javascript
POST /api/posts
{
  "title": "How to use Laravel Collections",
  "body": [{"type": "text", "content": "Here's a useful tip..."}],
  "snippets": [
    {
      "language": "php",
      "code_text": "$collection = collect([1, 2, 3]);",
      "block_index": 0
    }
  ],
  "tags": ["laravel", "php", "collections"]
}
```

### Add Inline Comment
```javascript
POST /api/posts/{postId}/comments
{
  "body": "Great explanation!",
  "snippet_id": 1,
  "start_line": 5,
  "end_line": 5,
  "is_inline": true
}
```

### Toggle Reaction
```javascript
POST /api/post/{postId}/reactions
{
  "reaction_type": "love"
}
```

## 🚧 TODO / Future Enhancements

- [ ] AI Code Summary endpoint (stub ready)
- [ ] AI Auto-Review suggestions (stub ready)
- [ ] Diff mode for code comparison
- [ ] GitHub PR import via webhook
- [ ] WebSocket real-time notifications (Laravel Echo)
- [ ] Background job for trending score calculation
- [ ] Bookmarks folders organization
- [ ] Advanced search with filters

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For issues and questions, please open an issue on GitHub.
