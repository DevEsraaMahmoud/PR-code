# PR Code

A full-stack Laravel + Vue 3 application for posting code snippets with inline comments, similar to GitHub PR reviews.

## Features

- **Authentication**: Email/password registration and login
- **Posts**: Create posts with mixed text and code blocks
- **Code Syntax Highlighting**: Automatic syntax highlighting using Prism.js
- **Inline Comments**: Comment on specific line ranges in code snippets
- **Threaded Comments**: Reply to comments to create discussion threads
- **Notifications**: Get notified when someone comments on your posts
- **Search**: Search posts by title or filter by programming language
- **Trending Posts**: Redis-cached trending posts based on recent activity

## Tech Stack

### Backend
- Laravel 12
- MySQL 8.0
- Redis (for caching)
- Laravel Sanctum (API authentication)

### Frontend
- Vue 3 (Composition API)
- TypeScript
- Pinia (state management)
- Vue Router
- Prism.js (syntax highlighting)
- Tailwind CSS
- Axios (HTTP client)

## Architecture

The application follows clean architecture principles:

- **Repository Pattern**: Data access layer abstraction
- **Service Layer**: Business logic separation
- **Form Requests**: Input validation
- **API Resources**: Consistent API responses
- **Events & Listeners**: Decoupled notification system
- **Policies**: Authorization logic

## Setup

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd pr-code
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Start Docker containers:
```bash
docker compose up -d
```

4. Install dependencies and setup:
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app npm install
docker compose exec app npm run build
```

5. Access the application:
- Frontend: http://localhost:8000
- API: http://localhost:8000/api

### Manual Setup

1. Install PHP dependencies:
```bash
composer install
```

2. Install Node dependencies:
```bash
npm install
```

3. Copy `.env.example` to `.env` and configure:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pr_code
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations:
```bash
php artisan migrate
```

6. Build frontend assets:
```bash
npm run build
```

7. Start development servers:
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires auth)
- `GET /api/me` - Get current user (requires auth)

### Posts
- `GET /api/posts` - List all posts (supports `?q=query` and `?language=lang`)
- `GET /api/posts/trending` - Get trending posts
- `GET /api/posts/{id}` - Get single post
- `POST /api/posts` - Create post (requires auth)
- `PUT /api/posts/{id}` - Update post (requires auth)
- `DELETE /api/posts/{id}` - Delete post (requires auth)

### Comments
- `GET /api/comments?snippet_id={id}` - Get comments for snippet
- `POST /api/comments` - Create comment (requires auth)
- `PUT /api/comments/{id}` - Update comment (requires auth)
- `DELETE /api/comments/{id}` - Delete comment (requires auth)

### Snippets
- `GET /api/snippets?post_id={id}` - Get snippets for post
- `GET /api/snippets/{id}` - Get single snippet

## Testing

Run tests:
```bash
php artisan test
```

## Project Structure

```
app/
├── Events/              # Application events
├── Http/
│   ├── Controllers/    # API controllers
│   ├── Requests/       # Form validation
│   └── Resources/     # API resources
├── Listeners/          # Event listeners
├── Models/             # Eloquent models
├── Policies/           # Authorization policies
├── Repositories/      # Data access layer
└── Services/           # Business logic layer

resources/
├── js/
│   ├── components/     # Vue components
│   ├── pages/          # Vue pages
│   ├── router/         # Vue Router config
│   └── stores/         # Pinia stores
└── views/              # Blade templates

database/
├── migrations/         # Database migrations
├── factories/          # Model factories
└── seeders/           # Database seeders
```

## License

MIT License
