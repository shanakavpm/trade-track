# Trade Track - Order Management System

[![Tests](https://img.shields.io/badge/tests-21%20passed-brightgreen)]() [![PHP](https://img.shields.io/badge/PHP-8.4-blue)]() [![Laravel](https://img.shields.io/badge/Laravel-12-red)]()

Enterprise-grade Laravel 12 order processing system with async workflows, KPI tracking, and Redis-backed analytics.

## ✨ Status: PRODUCTION READY

- ✅ **21/21 tests passing (100%)**
- ✅ All database schema issues fixed
- ✅ All original requirements met
- ✅ Idempotent refund processing
- ✅ Real-time KPI tracking
- ✅ Queue-based async processing

## 🚀 Quick Start

```bash
# One-command setup
./setup.sh
```

That's it! The application is ready at `http://localhost:8000`

## 📋 Features

### Task 1: Order Management ✅
- CSV import with queued processing (`php artisan orders:import file.csv`)
- Order workflow: reserve stock → simulate payment → finalize/rollback
- Daily KPIs: revenue, order count, average order value
- Customer leaderboard using Redis
- Laravel Horizon for queue monitoring

### Task 2: Notifications ✅
- Email notifications on order success/failure
- Queued notification jobs (non-blocking)
- Includes order_id, customer_id, status, total
- Notification history in database

### Task 3: Refunds ✅
- Partial and full refund support
- Asynchronous processing via queued jobs
- Real-time KPI and leaderboard updates
- Idempotency with UUID-based keys

## 🛠️ Tech Stack

- **Laravel 12** (PHP 8.4)
- **MariaDB 11.2** - Primary database
- **Redis 7** - Queues, cache, KPIs, leaderboards
- **Horizon** - Queue monitoring dashboard
- **Supervisor** - Process management
- **PHPUnit/Pest** - Testing framework

## 📦 Manual Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_DATABASE=trade_track
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🏃 Running the Application

```bash
# Start application server
php artisan serve

# Start Horizon queue worker (in another terminal)
php artisan horizon

# Start development server
php artisan serve

# View Horizon dashboard
http://localhost:8000/horizon

# Start Mailpit (for email testing)
mailpit
```

## Import Orders

```bash
# Copy sample CSV to storage
cp orders_sample.csv storage/app/imports/orders.csv

# Run import command
php artisan orders:import storage/app/imports/orders.csv

# Monitor progress in Horizon
http://localhost:8000/horizon
```

## API Endpoints

```bash
# Get today's KPIs
GET /api/kpi/today

# Get current month leaderboard
GET /api/leaderboard/current-month

# Mock payment callback (signed route)
POST /api/payments/mock/callback/{payment}?signature={sig}
```

## CSV Format

```csv
order_id,customer_id,sku,qty,unit_price
ORD-001,1,LAPTOP-001,2,1299.99
```

**Validation Rules:**
- `order_id` - Required, string, max 255
- `customer_id` - Required, integer, must exist in customers table
- `sku` - Required, string, must exist in products table
- `qty` - Required, integer, min 1, max 10000
- `unit_price` - Required, numeric, min 0, max 999999.99

## Order Workflow

1. **Import** → CSV chunked (100 rows), validated, queued
2. **Create Order** → Idempotency check, order + items created
3. **Reserve Stock** → Transactional stock decrement
4. **Payment** → Mock payment with signed callback
5. **Finalize** → Update KPIs, send notification
6. **Rollback** → Restore stock on failure

## Security Notes

- All money values use `DECIMAL(12,2)`
- Idempotency keys prevent duplicate orders/refunds
- Payment callbacks use HMAC signatures with 15min TTL
- Stock operations use pessimistic locking
- Jobs use `WithoutOverlapping` middleware
- All external inputs validated via FormRequests

## Performance Notes

- CSV streaming with chunked processing (100 rows)
- Bulk inserts where possible
- Redis for KPIs (90-day TTL)
- Separate queues: import, orders, notifications, refunds
- Exponential backoff: [10s, 30s, 60s]
- Database indexes on foreign keys, status, dates

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=ImportOrdersTest
php artisan test --filter=OrderWorkflowTest
php artisan test --filter=RefundIdempotencyTest
php artisan test --filter=KpiAndLeaderboardTest
php artisan test --filter=NotificationLogTest
```

## Troubleshooting

**Queue not processing?**
- Check Redis is running: `redis-cli ping`
- Restart Horizon: `php artisan horizon:terminate`
- Check failed jobs: `php artisan queue:failed`

**Stock reservation failing?**
- Verify product stock_quantity > 0
- Check for database locks
- Review logs: `storage/logs/laravel.log`

**Payment callback errors?**
- Verify signature matches
- Check callback hasn't expired (15min)
- Ensure route is signed: `URL::temporarySignedRoute()`

**KPIs not updating?**
- Check Redis connection
- Verify events are firing
- Run manual snapshot: `php artisan kpi:snapshot`

**Emails not sending?**
- Start Mailpit: `mailpit`
- Check MAIL_PORT=1025 in .env
- View emails: `http://localhost:8025`

## Commands

```bash
# Import orders from CSV
php artisan orders:import {file}

# Snapshot KPIs to database
php artisan kpi:snapshot [--date=2025-11-01]
```

## Architecture

**DTOs** - Type-safe data transfer (OrderImportRow, OrderProcessedPayload, RefundRequestPayload)

**Services** - Business logic (KpiService, StockService, MockPaymentService)

**Jobs** - Async processing with retry logic and backoff

**Events** - OrderProcessed, RefundProcessed

**Listeners** - QueueOrderNotification, UpdateKpisAndLeaderboard

**Middleware** - WithoutOverlappingOrder (prevents concurrent processing)

## License

MIT
