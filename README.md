# Store Inventory

Laravel inventory and order management API with Sanctum authentication, a Blade login page, and concurrency-safe stock deduction.

Authentication uses the `users` table. The `username` column is not used. Existing inventory tables are expected to already exist; migrations skip them when present and only create supporting tables such as `personal_access_tokens` and `jobs`.

Step 1 : Manualy Install Composer
step 2 : cmd command prompt(composer create-project laravel/laravel Store-Inventory)
Step 3 : Th Heidisql Manualy Create database and Create.
php project run command : php -S localhost:8000 -t public

## Demo Video

[Watch the Store Inventory Demo Video](https://raveenalatha.github.io/store_inventory_demovideo/)

The demo covers:

- User login
- Product catalog and stock display
- Creating a customer order
- Order total and tax calculation
- Automatic stock deduction
- Insufficient stock validation
- Order history by customer email
- Low-stock product display
- Queue worker and confirmation mail logging
- Automated test execution


## 1. Requirements

- PHP 7.4 or 8.0+
- Composer
- MySQL 5.7+ / 8.0 (InnoDB) for production and concurrency locking
- PHP extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath

## 2. PHP version

This project targets **PHP 7.4+** (Laravel 8). PHP 8.0 and 8.1 also work.

## 3. Composer installation

```bash
composer install
```

## 4. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

## 5. Database configuration

Update `.env`:

```env
APP_NAME="Store Inventory"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store_inventory
DB_USERNAME=root
DB_PASSWORD=

DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_general_ci

QUEUE_CONNECTION=database
LOW_STOCK_THRESHOLD=10
```

Create the MySQL database if it does not already exist:

```sql
CREATE DATABASE store_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

## 6. Migration commands

```bash
php artisan migrate
```

Existing inventory tables are **not** recreated. Fresh installs (and PHPUnit sqlite) still get the full schema from the same migration files.

```bash
php artisan migrate --seed
```

## 7. Seeder commands

```bash
php artisan db:seed
```

Seeded data:

- Demo admin in `users`: `admin@example.com` / `password`
- Existing staff accounts are copied from `hrm_profile` into `users` (without `username`)
- 5 customers
- 12 products with mixed prices, tax rates, and stock levels
- Sample orders created through the same order service used by the API

## 8. Sanctum setup

Laravel Sanctum is already required. After migrate, the `personal_access_tokens` table exists.

API login issues a Bearer token. Send it on protected routes:

```http
Authorization: Bearer {token}
```

## 9. Queue setup

Order confirmation uses `SendOrderConfirmationJob` (`ShouldQueue`). It writes to the Laravel log instead of sending a real email.

`.env`:

```env
QUEUE_CONNECTION=database
```

Then:

```bash
php artisan queue:work
```

The job is dispatched only after the order transaction commits.

## 10. How to run the application

```bash
php artisan serve
```

Open:

- Login: http://localhost:8000/login
- Dashboard: http://localhost:8000/dashboard
- API: http://localhost:8000/api

Web demo login:

- Email: `admin@example.com`
- Password: `password`

Accounts copied into `users` (for example Raveena) can also sign in with their own email and password.

## 11. How to run tests

```bash
php artisan test
```

Or:

```bash
vendor/bin/phpunit
```

Tests use an in-memory SQLite database and do **not** touch `store_inventory`.

## 12. API endpoints

| Method | Endpoint | Auth | Description |
| --- | --- | --- | --- |
| POST | `/api/login` | No | Issue a Sanctum token |
| POST | `/api/logout` | Yes | Revoke the current token |
| GET | `/api/me` | Yes | Current user |
| POST | `/api/orders` | Yes | Create an order |
| GET | `/api/orders/history?email=` | Yes | Customer order history |
| GET | `/api/products/low-stock` | Yes | Products below `LOW_STOCK_THRESHOLD` |

Web:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/login` | Login page |
| POST | `/login` | Session login |
| POST | `/logout` | Session logout |
| GET | `/dashboard` | Inventory summary |

## 13. Example API requests and responses

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@example.com\",\"password\":\"password\"}"
```

```json
{
  "message": "Login successful.",
  "data": {
    "token": "1|xxxxxxxx",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "active": true,
      "last_login": "2026-09-11 10:00:00"
    }
  },
  "errors": {}
}
```

### Create order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d "{\"customer_name\":\"Raveena\",\"customer_email\":\"raveena@example.com\",\"items\":[{\"product_id\":1,\"quantity\":2},{\"product_id\":3,\"quantity\":1}]}"
```

Success (`201`):

```json
{
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "status": "confirmed",
    "subtotal": 130,
    "tax_amount": 11.5,
    "total_amount": 141.5,
    "created_at": "2026-09-11 10:00:00",
    "customer": {
      "id": 1,
      "name": "Raveena",
      "email": "raveena@example.com"
    },
    "items": []
  },
  "errors": {}
}
```

Insufficient stock (`422`):

```json
{
  "message": "Insufficient stock",
  "data": {},
  "errors": {
    "product_id": [
      "Only 2 units are available for this product."
    ]
  }
}
```

Prices and tax rates in the request body are ignored. They are always read from `products`.

### Order history

```bash
curl "http://localhost:8000/api/orders/history?email=raveena@example.com" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

Unknown customer: HTTP `404`.

### Low stock

```bash
curl http://localhost:8000/api/products/low-stock \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

```json
{
  "message": "Low stock products retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Keyboard",
      "sku": "KEY001",
      "stock_quantity": 4
    }
  ],
  "errors": {}
}
```

### Logout / current user

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"

curl http://localhost:8000/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

Unauthenticated requests to protected routes return HTTP `401`.

## 14. How concurrency protection works

Order creation runs inside `DB::transaction()`. Product rows are loaded with `lockForUpdate()` in ascending `id` order, then stock is checked, the order is written, and stock is decremented. The row lock is held until commit.

If stock is `1` and two requests each ask for quantity `1`, one request gets `201` and the other gets `422`. Stock never goes negative and no partial order is stored.

This requires **MySQL InnoDB**. SQLite (used in PHPUnit) does not apply `FOR UPDATE` the same way. Sequential tests still prove that a second order cannot oversell after the first commit.

### Manual concurrent request test

1. Set a product's `stock_quantity` to `1`.
2. Log in and copy the Bearer token.
3. Run two requests at the same time (two terminals):

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d "{\"customer_name\":\"A\",\"customer_email\":\"a@example.com\",\"items\":[{\"product_id\":PRODUCT_ID,\"quantity\":1}]}"
```

Expected: exactly one `201`, one `422`, final stock `0`, one order item.

## 15. How to configure LOW_STOCK_THRESHOLD

`.env`:

```env
LOW_STOCK_THRESHOLD=10
```

`config/inventory.php` reads that value. Products with `stock_quantity < LOW_STOCK_THRESHOLD` appear on `GET /api/products/low-stock` and on the dashboard. Change the env value and run:

```bash
php artisan config:clear
```

No application code change is required.

## Exact commands to install and run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work
php artisan test
```

Run `queue:work` in a second terminal while `php artisan serve` is running.
