# CS Cloth

CS Cloth is a role-based merchandise storefront with:
- `backend/`: Laravel 12 API
- `frontend/`: SvelteKit 2 app

The repository no longer includes a Laravel-served frontend. The backend is API-only.

## Current App Surface

Frontend routes:
- `/items`
- `/items/[id]`
- `/cart`
- `/checkout`
- `/orders`
- `/wallet`
- `/questions`
- `/profile`
- `/admin/items`
- `/admin/orders`
- `/admin/questions`
- `/superadmin/reports`
- `/superadmin/admins`
- `/superadmin/users`

API routes:
- `/api/auth/*`
- `/api/items*`
- `/api/cart*`
- `/api/orders*`
- `/api/wallet*`
- `/api/admin/*`
- `/api/superadmin/*`

## Stack

- Backend: Laravel 12, PHP 8.2+, MySQL, Redis, Mailpit
- Frontend: SvelteKit 2, Svelte 5, TypeScript, Vite
- Styling: Tailwind CSS

## Clone And Run

Requirements:
- Docker Desktop or Docker Engine with Compose support
- `bash`

1. Clone the repository.

```bash
git clone https://github.com/SkyBlueFox/CS-Cloth.git
cd CS-Cloth
```

2. Create the env files used by the current stack.

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

3. If you want real OTP emails through Gmail, update these values in `backend/.env`:

```bash
MAIL_USERNAME=your-google-account@gmail.com
MAIL_PASSWORD=your-google-app-password (you can generate one here: https://myaccount.google.com/apppasswords)
MAIL_FROM_ADDRESS="your-google-account@gmail.com"
```

If you do not change them, Mailpit is still available for local mail inspection.

4. Start the stack.

```bash
./compose up -d
```

5. Initialize the database and storage.

```bash
./compose exec laravel-api php artisan key:generate
./compose exec laravel-api php artisan migrate:fresh --seed
./compose exec laravel-api php artisan storage:link
```

6. Open the app.

- Frontend: `http://localhost:3000`
- Backend API root: `http://localhost`
- Mailpit: `http://localhost:8025`

Useful commands:

```bash
./compose down
./compose logs -f
./compose up -d --build
```

## Env Files

Required:
- `backend/.env`
- `frontend/.env`

Optional:
- root `.env`

The root `.env` is not required for the current application. The `./compose` wrapper only sources it if present so you can override shell-exported values such as `WWWUSER` and `WWWGROUP`. If you do nothing, the wrapper already falls back to your current user/group IDs.

## Notes

- `backend/.env.example` is the Docker-oriented template used by the current stack.
- `frontend/.env.example` should keep `BACKEND_URL=http://laravel-api` for Docker networking.
- Uploaded item images are served from `backend/storage/app/public/items`.
- The frontend entry point is `/items`. The backend root is only an API status response.

## Seeded Demo Accounts

After `./compose exec laravel-api php artisan migrate:fresh --seed`:

- Superadmin: `tan@cloth.com` / `asd123`
- Admin: `admin@cloth.com` / `asd123`
- User: `sbkyajeg4312@gmail.com` / `asd123`
- User: `may@cloth.com` / `asd123`
- User: `toruplaytube@gmail.com` / `asd123`
- User: `somchai@cloth.com` / `asd123`

## Screenshots

### Storefront Catalog

![Storefront catalog](docs/screenshots/storefront-items.png)

### Item Detail

![Item detail page](docs/screenshots/item-detail.png)

### Cart And Checkout

![Cart and checkout](docs/screenshots/cart-checkout.png)

### Item's Q&A Section

![Customer questions](docs/screenshots/questions.png)

### Customer's Wallet

![Wallet page](docs/screenshots/wallet.png)

### Admin Inventory Management

![Admin inventory](docs/screenshots/admin-items.png)

### Admin Orders Management

![Admin orders](docs/screenshots/admin-orders.png)

### Admin Questions Answering

![Admin question moderation](docs/screenshots/admin-questions.png)

### Superadmin Reports Management

![Superadmin reports](docs/screenshots/superadmin-reports.png)

### Superadmin User Management

![Superadmin users](docs/screenshots/superadmin-users.png)
