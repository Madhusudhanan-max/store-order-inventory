#!/usr/bin/env bash
#
# Manual check that two simultaneous orders for the same product, with only
# one unit left in stock, don't both succeed.
#
# PHPUnit runs in a single process, so it can't fire two real concurrent HTTP
# requests against the same server — that's why this isn't an automated test.
# Run this against a locally running `php artisan serve` instead.
#
# Usage:
#   1. php artisan serve
#   2. Set a product's stock to 1, e.g. via tinker:
#        php artisan tinker
#        >>> App\Models\Product::first()->update(['stock' => 1]);
#   3. Replace PRODUCT_ID below with that product's id.
#   4. bash scripts/concurrency_check.sh

PRODUCT_ID=1
URL="http://127.0.0.1:8000/api/orders"

BODY='{
  "customer_email": "race1@example.com",
  "customer_name": "Race One",
  "items": [{"product_id": '"$PRODUCT_ID"', "quantity": 1}]
}'

echo "Firing two requests at once for product $PRODUCT_ID..."

curl -s -o /tmp/resp1.json -w "Request 1 -> %{http_code}\n" \
  -X POST "$URL" -H "Content-Type: application/json" -d "$BODY" &

curl -s -o /tmp/resp2.json -w "Request 2 -> %{http_code}\n" \
  -X POST "$URL" -H "Content-Type: application/json" -d "$BODY" &

wait

echo ""
echo "Expected: one request returns 201, the other returns 422."
echo "Response 1:"; cat /tmp/resp1.json; echo ""
echo "Response 2:"; cat /tmp/resp2.json; echo ""
