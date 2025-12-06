# PR Code - Production Implementation Plan

## Current Status ✅

### Completed (M1 Partial)
- ✅ Database migrations (posts, snippets, comments, notifications)
- ✅ Basic models with relationships
- ✅ Repository pattern (PostRepository, SnippetRepository, CommentRepository)
- ✅ Service layer (PostService, CommentService)
- ✅ API controllers with basic CRUD
- ✅ Inertia.js setup with Vue 3
- ✅ Basic Inertia pages (Home, Posts/Show, Posts/Create, Auth)

### Enhanced Migrations (Just Added)
- ✅ Posts: slug, visibility, meta JSON
- ✅ Tags table with slug
- ✅ Post-Tag pivot table
- ✅ Likes table (polymorphic)
- ✅ Comments: enhanced for inline comments (post_id, is_inline, edited_at)
- ✅ Fulltext indexes on posts

## Remaining Work by Milestone

### M1 (MVP - Core) - In Progress
- [ ] Update PostService to handle tags, code_blocks, visibility, pagination
- [ ] Update CommentService to handle inline comments with line validation
- [ ] Enhance API controllers with pagination, proper error handling
- [ ] Build CodeViewer component with PrismJS line numbers
- [ ] Build CommentThread component for nested comments
- [ ] Update Inertia pages to use new structure
- [ ] Add policies for authorization

### M2 (UX + Inline Comments)
- [ ] Add line selection UI in CodeViewer
- [ ] Implement InlineAnnotation component with badges
- [ ] Add line range validation in backend
- [ ] Wire inline comment creation flow

### M3 (Realtime & Notifications)
- [ ] Set up Laravel Broadcasting (Pusher/WebSockets)
- [ ] Create CommentCreatedBroadcast event
- [ ] Implement Laravel Echo on frontend
- [ ] Queue notification listeners
- [ ] Build notifications UI

### M4 (Search & Caching)
- [ ] Implement SearchService with MySQL Fulltext
- [ ] Add search API endpoint
- [ ] Add Redis caching for popular posts
- [ ] Implement search UI component

### M5 (Tests & CI)
- [ ] Feature tests for posts, comments, search
- [ ] Unit tests for services
- [ ] GitHub Actions CI workflow
- [ ] Frontend tests (Jest/Vitest)

### M6 (Polish & Docs)
- [ ] Comprehensive README
- [ ] API documentation (Swagger/Postman)
- [ ] Database seeders
- [ ] Deployment guide
- [ ] ADRs (Architecture Decision Records)

## File Checklist

### Backend Files Created/Updated
- ✅ `database/migrations/*` - All migrations
- ✅ `app/Models/*` - All models with relationships
- ✅ `app/Repositories/*` - Repository layer
- ✅ `app/Services/*` - Service layer
- ✅ `app/Http/Controllers/Api/*` - API controllers
- ✅ `app/Http/Controllers/Web/*` - Web controllers (Inertia)
- ✅ `app/Http/Requests/*` - Form requests
- ✅ `app/Http/Resources/*` - API resources
- ✅ `app/Policies/*` - Authorization policies
- ✅ `app/Events/*` - Events
- ✅ `app/Listeners/*` - Event listeners

### Frontend Files Created/Updated
- ✅ `resources/js/app.js` - Inertia setup
- ✅ `resources/js/Layouts/AppLayout.vue` - Main layout
- ✅ `resources/js/Pages/Home.vue` - Home page
- ✅ `resources/js/Pages/Posts/*` - Post pages
- ✅ `resources/js/Pages/Auth/*` - Auth pages
- ✅ `resources/js/Components/*` - Vue components

### Configuration Files
- ✅ `routes/web.php` - Web routes (Inertia)
- ✅ `routes/api.php` - API routes
- ✅ `vite.config.js` - Vite configuration
- ✅ `docker-compose.yml` - Docker setup
- ✅ `Dockerfile` - PHP 8.4 container

## Next Immediate Steps

1. **Run migrations** to apply new schema
2. **Update PostService** to handle tags, visibility, pagination
3. **Update CommentService** to handle inline comments properly
4. **Enhance CodeViewer** component with line selection
5. **Add policies** for authorization
6. **Test the flow** end-to-end

## API Endpoints Status

### ✅ Implemented
- POST /api/register
- POST /api/login
- POST /api/logout
- GET /api/me
- GET /api/posts
- POST /api/posts
- GET /api/posts/{id}
- PUT /api/posts/{id}
- DELETE /api/posts/{id}
- GET /api/comments
- POST /api/comments

### ⏳ Needs Enhancement
- GET /api/posts (add pagination, filters)
- GET /api/posts/{slug} (use slug instead of id)
- POST /api/posts/{post_id}/comments (nested route)
- GET /api/notifications
- POST /api/notifications/mark-read
- GET /api/search

### ⏳ New Endpoints Needed
- POST /api/posts/{id}/like
- DELETE /api/posts/{id}/like
- GET /api/posts/{id}/likes
- GET /api/tags
- GET /api/tags/{slug}/posts

## Database Schema Status

### ✅ Tables Created
- users
- posts (enhanced with slug, visibility, meta)
- snippets (code_blocks)
- comments (enhanced for inline)
- notifications
- tags
- post_tag
- likes

### ⏳ Indexes Needed
- Fulltext on posts.title (✅ added)
- Indexes on comments (✅ added)
- Composite indexes for performance

## Testing Status

### ⏳ Tests Needed
- Feature tests for post CRUD
- Feature tests for comment creation (inline & regular)
- Feature tests for search
- Unit tests for PostService
- Unit tests for CommentService
- Unit tests for SearchService
- Frontend component tests
- E2E tests for critical flows

## Deployment Status

### ✅ Docker Setup
- docker-compose.yml with app, nginx, mysql, redis
- Dockerfile with PHP 8.4
- Environment configuration

### ⏳ CI/CD Needed
- GitHub Actions workflow
- Build and test automation
- Deployment pipeline

