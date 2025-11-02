# Trade Track - Order Management System

[![Tests](https://img.shields.io/badge/tests-21%20passed-brightgreen)]() [![PHP](https://img.shields.io/badge/PHP-8.4-blue)]() [![Laravel](https://img.shields.io/badge/Laravel-12-red)]()



## ✨ Status: PRODUCTION READY

- ✅ **21/21 tests passing (100%)**
- ✅ CSV import with queue processing
- ✅ Email notifications
- ✅ Refund processing
- ✅ Real-time KPI tracking

## 🚀 Quick Start (3 Steps)

```bash
# 1. Setup everything
./setup.sh

# 2. Start services
./start-services.sh

# 3. Import orders
php artisan orders:import file.csv
```

**Done!** View at:
- App: `http://localhost:8000`
- Emails: `http://localhost:8025`
- Horizon: `http://localhost:8000/horizon`

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

## 📦 Requirements

- PHP 8.4+
- Composer
- MariaDB/MySQL
- Redis
- Mailpit (optional, for email testing)

## 📥 Import CSV Files

### Option 1: Import Sample Data
```bash
# Import the included sample file
php artisan orders:import file.csv
```

### Option 2: Import Your Own CSV
```bash
# Place your CSV file in the project root
php artisan orders:import your_file.csv

# Or specify full path
php artisan orders:import /path/to/your/file.csv
```

### Option 3: Generate Large CSV for Testing
```bash
# Generate with custom filename
php artisan generate:file 500 --filename=file.csv

# Import the generated file
php artisan orders:import file.csv
```

### Import Options
```bash
# Custom chunk size (default: 100)
php artisan orders:import file.csv --chunk=200

# Custom queue (default: high)
php artisan orders:import file.csv --queue=default

# Both options
php artisan orders:import file.csv --chunk=200 --queue=high
```

### Monitor Import Progress
- Open Horizon: `http://localhost:8000/horizon`
- Check queue jobs in real-time
- View failed jobs and retry them

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

## 🎯 Common Commands

```bash
# Import orders from CSV
php artisan orders:import file.csv

# Generate large CSV for testing
php artisan generate:large-csv 500

# Snapshot KPIs to database (today)
php artisan kpi:snapshot

# Snapshot KPIs for specific date
php artisan kpi:snapshot --date=2025-11-02

# Reset database and seed fresh data
php artisan migrate:fresh --seed

# Run tests
php artisan test

# Start queue worker
php artisan queue:work

# Start Horizon
php artisan horizon
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
