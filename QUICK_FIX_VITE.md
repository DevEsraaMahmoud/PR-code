# Quick Fix for Vite Connection Error

## Problem
The browser is trying to connect to Vite dev server on port 5174, but it's not running.

## Solution 1: Use Built Assets (Immediate Fix)

Build the assets so the app works without Vite dev server:

```bash
docker compose exec app npm run build
```

Then refresh your browser - it should work!

## Solution 2: Start Vite Dev Server (For Development)

Start Vite dev server for hot reloading:

```bash
docker compose exec -d app npm run dev
```

Wait a few seconds, then refresh your browser.

## Solution 3: Check Port Configuration

The error shows port 5174, but we configured 5173. Check if there's a port conflict:

```bash
docker compose exec app netstat -tuln | grep 517
```

## Why This Happens

- In development mode, Laravel tries to use Vite dev server for hot reloading
- If Vite isn't running, you get connection refused errors
- The app will work fine with built assets (Solution 1)

## Recommended Approach

For now, use **Solution 1** (build assets) to get the app working immediately. Then set up Vite dev server later for development.


