# Frontend Build & Run Guide

The React app is located in `ui/` and uses Vite.

## Prerequisites
- Node.js 18+
- npm 9+

## Install
```bash
cd ui
npm install
```

## Run in development
```bash
npm run dev
```
- Default URL: `http://localhost:5173`

## Production build
```bash
npm run build
```
- Output directory: `ui/dist`

## Preview production build
```bash
npm run preview
```

## Notes
- API calls are currently made to `/api/index.php` from the frontend code.
- If developing frontend separately, configure a proxy or run behind the same host where `/api` is reachable.
