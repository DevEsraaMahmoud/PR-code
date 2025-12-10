# Setup Guide - PR Code Social Platform

This guide will walk you through setting up the PR Code platform from scratch.

## Step-by-Step Setup

### 1. Database Setup

Create a MySQL database:
```sql
CREATE DATABASE pr_code CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- users (with profile fields)
- posts
- snippets
- comments (with resolved field)
- reactions
- follows
- bookmarks
- snippet_versions
- notifications
- tags
- likes

### 3. Seed Demo Data (Optional)

Create a seeder file:
```bash
php artisan make:seeder DatabaseSeeder
```

Then run:
```bash
php artisan db:seed
```

### 4. Frontend Setup

Install dependencies:
```bash
npm install
```

Build assets:
```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

### 5. Configure Sanctum

Ensure `.env` has:
```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:8000
SESSION_DRIVER=cookie
```

### 6. Create Test User

You can use Tinker:
```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);
```

### 7. Test API Endpoints

Use Postman or curl to test:

**Register:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**Create Post (with token):**
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"My First Post","body":[{"type":"text","content":"Hello world!"}]}'
```

## Common Issues

### Migration Errors
If you get foreign key errors, ensure tables are created in order. The migrations are timestamped to run in the correct order.

### Sanctum Auth Issues
Make sure:
1. `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
2. CORS is configured in `config/cors.php`
3. Session middleware is enabled

### Frontend Build Errors
Clear cache and rebuild:
```bash
npm run clean
npm install
npm run build
```

## Next Steps

1. Create your first post via API or UI
2. Add code snippets to posts
3. Test inline comments
4. Try reactions and follows
5. Explore the feed with sorting/filtering

## Development Tips

- Use `php artisan route:list` to see all available routes
- Use `php artisan tinker` to interact with models
- Check `storage/logs/laravel.log` for errors
- Use Vue DevTools for frontend debugging


