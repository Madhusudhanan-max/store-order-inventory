<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from validated request data:
     * [
     *   'customer_email' => string,
     *   'customer_name' => string,
     *   'items' => [['product_id' => int, 'quantity' => int], ...],
     * ]
     *
     * @throws InsufficientStockException
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['customer_email']],
                ['name' => $data['customer_name']]
            );

            // Lock the product rows we're about to touch so two orders for the
            // same product can't both read the same stock figure and oversell.
            // The second transaction simply waits here until the first commits.
            $productIds = collect($data['items'])->pluck('product_id')->unique();

            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $tax = 0;
            $preparedLines = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];
                $quantity = (int) $item['quantity'];

                if ($product->stock < $quantity) {
                    throw new InsufficientStockException($product, $quantity);
                }

                $lineSubtotal = $product->price * $quantity;
                $lineTax = round($lineSubtotal * $product->tax_percentage / 100, 2);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $tax += $lineTax;

                $preparedLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $subtotal + $tax,
            ]);

            foreach ($preparedLines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            SendOrderConfirmationEmail::dispatch($order);

            return $order->load('items.product', 'customer');
        });
    }
}
