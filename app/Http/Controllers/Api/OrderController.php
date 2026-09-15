<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order, 201);
    }

    public function history(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = Customer::where('email', $request->query('email'))->first();

        if (! $customer) {
            return response()->json(['message' => 'No customer found with that email.'], 404);
        }

        $orders = $customer->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders);
    }
}
