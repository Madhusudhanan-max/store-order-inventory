<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Store Order & Inventory Mini-System

This is my submission for the Laravel Developer mini task from Mallow Technologies.
It's a small API for a retail counter — record customer orders against a product
catalog, keep stock accurate, and give the counter a way to check what's running low.

## Setup

I built this on top of a fresh Laravel 11 install rather than shipping the whole
framework, so you'll need to do this once:

```bash
composer create-project laravel/laravel mallow-tech-task "^11.0"
cd mallow-tech-task
```

```bash
composer install
php artisan key:generate
```

One thing to watch for: newer Laravel versions default `SESSION_DRIVER` to
`database`, which needs a `sessions` table you probably won't have yet. Since
this is a pure API project with no web sessions, I'd just set:

```
SESSION_DRIVER=file
```

in `.env` and skip that problem entirely. Then:

```bash
php artisan migrate --seed
php artisan serve
```

To see the confirmation "email" actually fire, either set
`QUEUE_CONNECTION=sync` for a quick demo, or keep the database queue and run
`php artisan queue:table && php artisan migrate` followed by
`php artisan queue:work` in a second terminal.

## What's in the schema

- **customers** — name, email (unique)
- **products** — name, code (unique), price, tax_percentage, stock
- **orders** — belongs to a customer, holds subtotal / tax / grand_total
- **order_items** — belongs to an order and a product, and stores the
  quantity, unit_price, tax_amount and line_total *at the time of the order*

I went with snapshotting price and tax on the order item rather than always
pulling live values off the product. If a product's price changes six months
later, past orders shouldn't silently change too.

## API

**POST /api/orders**

```json
{
  "customer_email": "madhu@example.com",
  "customer_name": "Madhu",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 4, "quantity": 1 }
  ]
}
```

Returns the created order with its items and products on 201. If any line
doesn't have enough stock, you get a 422 with a plain message telling you
which product and how much was available.

**GET /api/orders/history?email=madhu@example.com**

Returns that customer's orders, newest first, with items and products
loaded. 404 if the email doesn't match anyone.

**GET /api/products/low-stock?min_stock=10**

Products with stock below the min_stock. min_stock defaults to 10 if you
leave it off.

## Decisions I made where the brief was open-ended

**customer_name is required on every order, even for repeat customers.**
I thought about only requiring it when the customer doesn't exist yet, but
that means validating against the database inside a form request, which
felt messier than it needed to be for what this task is testing. If the
email already matches a customer, I just ignore the name in the request and
keep whatever's stored — didn't want an order accidentally renaming someone.

**Stock safety under concurrent orders** is done with `lockForUpdate()` on
the product rows inside a DB transaction. If two orders for the same
product land at the same time, the second one's query blocks at the
database level until the first transaction is done, so it always checks
against up-to-date stock before deciding whether to proceed or fail. This
needs a real database engine (MySQL/Postgres) — SQLite doesn't lock rows
the same way, which matters for the next point.

**No automated test for concurrency.** I could have written a PHPUnit test
that looks like it proves this, but it wouldn't actually be testing
anything — a single PHP process running the test suite can't fire two
genuinely overlapping HTTP requests. Instead there's `scripts/concurrency_check.sh`,
which fires two real curl requests at once against a running `php artisan serve`
instance with a product that only has 1 unit left. One comes back 201, the
other comes back 422. I'd rather have an honest manual check than a fake
automated one.

**No UI.** The brief calls the wireframe a "suggested reference" and says
explicitly that styling and layout are up to me and they're evaluating the
logic, not the design. The actual functional requirements list only API
endpoints, so I kept this API-only and demo it with Postman in the
recording.

**Tax is calculated per line, not on the order total** — since different
products can have different tax percentages, summing pre-computed line
totals is the only way that's actually correct.

**Low-stock min_stock is a query param, not a config value**, so whoever's
using this can adjust it per request without needing a deploy.

## Tests

- order creation: correct totals, stock gets deducted, the job gets
  dispatched
- insufficient stock: 422, stock left untouched, job not dispatched
- request validation
- order history, including the unknown-email case
- low-stock endpoint, with and without a min stock

