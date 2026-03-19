# CS Cloth

This repository contains:

- `backend/`: Laravel backend API
- `frontend/`: SvelteKit frontend

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+
- npm
- SQLite

## Fresh Setup

1. Clone the repository and enter it:

```bash
git clone <your-repo-url>
cd CS-Cloth
```

2. Set up the Laravel backend:

```bash
cd backend
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

3. Start the Laravel backend:

```bash
cd backend
php artisan serve
```

The backend will run at `http://127.0.0.1:8000`.

4. Set up the SvelteKit frontend in a second terminal:

```bash
cd frontend
npm install
cp .env.example .env
```

5. Start the frontend:

```bash
cd frontend
npm run dev
```

The frontend will run at `http://127.0.0.1:5173`.

## Default Seed Accounts

- `SUPERADMIN`
  - email: `tan@cloth.com`
  - password: `asd123`
- `ADMIN`
  - email: `admin@cloth.com`
  - password: `asd123`
- `USER`
  - email: `user@cloth.com`
  - password: `asd123`

## Notes

- Use `backend/.env` for Laravel configuration.
- Use `frontend/.env` for SvelteKit configuration.
- The root `.env` is not part of the intended app setup.
- Uploaded item images are stored under `backend/storage/app/public/items`.
- If you want a clean local reset, run:

```bash
cd backend
php artisan migrate:fresh --seed
```
