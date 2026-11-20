# Flash Sale Engine

A simple Laravel REST API for managing product reservations and orders during flash sales.

The main goal is to prevent overselling when multiple users try to reserve the same product at the same time.

## Tech Stack

- PHP 8.3+
- Laravel 13
- PostgreSQL 16
- Redis 7
- Docker & Docker Compose
- Nginx

## Features

- List products and available stock.
- Reserve a product for 5 minutes.
- Convert a valid reservation into an order.
- Reject expired reservations.
- Prevent overselling with database transactions and row locking.
- Cache product data using Redis.
- Process order-related jobs using Redis Queue.
- Automatically expire reservations using Laravel Scheduler.

## Installation

Clone the project:

```bash
git clone <repository-url>
cd flash-sale
```

Create the environment file:

```bash
cp .env.example .env
```

Start Docker services:

```bash
docker compose up -d --build
```

Generate the application key:

```bash
docker compose exec app php artisan key:generate
```

Run migrations and seeders:

```bash
docker compose exec app php artisan migrate --seed
```

Application URL:

```text
http://localhost:8080
```

## API Endpoints

### Get Products

```http
GET /api/products
```

Returns products with available stock.

### Reserve a Product

```http
POST /api/products/{id}/reserve
```

Request:

```json
{
    "user_id": 1
}
```

Creates a reservation for 5 minutes.

### Place an Order

```http
POST /api/orders
```

Request:

```json
{
    "reservation_id": 1,
    "user_id": 1
}
```

Converts a valid reservation into an order.

## Concurrency Handling

The application uses database transactions and `lockForUpdate()` to protect inventory.

Each inventory operation locks the product row before checking or changing stock.

This prevents multiple users from reserving the same available unit.

PostgreSQL is used for database-level locking.

## Background Jobs

- Laravel Queue processes order-related jobs.
- Laravel Scheduler expires old reservations every minute.

## Running Tests

Run all tests:

```bash
docker compose exec app php artisan test
```

Run a specific test:

```bash
docker compose exec app php artisan test --filter=FlashSaleTest
```

Tests cover reservation, order creation, expired reservations, and inventory limits.

## Docker Services

| Service | Purpose |
|---|---|
| app | Laravel application |
| nginx | Web server |
| postgres | Database |
| redis | Cache and queue |
| queue | Background jobs |
| scheduler | Reservation expiration |
