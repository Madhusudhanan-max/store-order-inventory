<?php

namespace App\Exceptions;

use App\Models\Product;
use Exception;

class InsufficientStockException extends Exception
{
    public Product $product;
    public int $requestedQuantity;

    public function __construct(Product $product, int $requestedQuantity)
    {
        $this->product = $product;
        $this->requestedQuantity = $requestedQuantity;

        parent::__construct(
            "Not enough stock for '{$product->name}'. Requested {$requestedQuantity}, only {$product->stock} available."
        );
    }
}
