# Vite Development Server Setup

## Understanding the Error

The error `GET http://[::1]:5173/@vite/client net::ERR_CONNECTION_REFUSED` occurs because:

1. **Vite dev server is not running** - The application is trying to connect to Vite's HMR (Hot Module Replacement) server on port 5173
2. **Development mode** - When `APP_DEBUG=true`, Laravel tries to use Vite dev server for live reloading

## Solutions

### Option 1: Run Vite Dev Server (Recommended for Development)

Start the Vite dev server inside the Docker container:

```bash
docker compose exec -d app npm run dev
```

This will:
- Start Vite dev server on port 5173
- Enable hot module replacement
- Allow live code changes without page refresh

### Option 2: Use Built Assets (Production Mode)

If you want to use pre-built assets instead:

1. **Build the assets:**
   ```bash
   docker compose exec app npm run build
   ```

2. **Set production mode** (optional):
   ```bash
   docker compose exec app bash -c "sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env"
   ```

## Current Configuration

- **Vite Port**: 5173 (exposed in docker-compose.yml)
- **Vite Host**: 0.0.0.0 (accessible from host)
- **HMR Host**: localhost (for browser connection)

## Quick Start Commands

### Development (with hot reload):
```bash
# Start all services
docker compose up -d

# Start Vite dev server
docker compose exec -d app npm run dev

# Access: http://localhost:8000
```

### Production (using built assets):
```bash
# Build assets
docker compose exec app npm run build

# Access: http://localhost:8000
```

## Troubleshooting

If you still see connection errors:

1. **Check if Vite is running:**
   ```bash
   docker compose exec app ps aux | grep vite
   ```

2. **Check Vite logs:**
   ```bash
   docker compose logs app | grep vite
   ```

3. **Restart Vite:**
   ```bash
   docker compose exec app pkill -f vite
   docker compose exec -d app npm run dev
   ```

4. **Verify port is exposed:**
   ```bash
   docker compose ps
   # Should show: 0.0.0.0:5173->5173/tcp
   ```

## Note

The application will work with **built assets** even without Vite dev server running. The dev server is only needed for:
- Hot module replacement
- Faster development iteration
- Live code changes

For production deployments, always use `npm run build` and serve the built assets.

