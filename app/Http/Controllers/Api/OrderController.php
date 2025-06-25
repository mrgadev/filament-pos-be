<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index() {
        $orders  = Order::with(['payment_method', 'order_products'])->get();

        $orders->transform(function ($order) {
            $order->payment_method_name = $order->payment_method ? $order->payment_method->name : '-';
            $order->order_products->transform( function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? '-',
                    'quantity' => $item->quantity ?? 0,
                    'unit_price' => $item->unit_price ?? 0,
                ];
            });
            return $order;
        });

        return response()->json([
            'data' => $orders,
            'message' => 'Orders retrieved successfully',
            'success' => true,
        ], 200);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'gender' => 'nullable|string|in:male,female',
            'birthday' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'success' => false,
            ], 422);
        }

        foreach($request->items as $item) {
            $product = Product::find($item['product_id']);
            if(!$product || $product->stock < $item['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $item['product_id'],
                    'success' => false,
                ], 422);
            }    
        }

        $order = Order::create($request->only([
            'name', 
            'email',
            'gender',
            'phone',
            'total_price',
            'notes',
            'payment_method_id',
            'paid_amount',
            'change_amount'
        ]));

        foreach($request->items as $item) {
            $order->order_products()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);

            // Update product stock
            // $product->decrement('stock', $item['quantity']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully create order',
            'data' => $order
        ], 200);
    }
}
