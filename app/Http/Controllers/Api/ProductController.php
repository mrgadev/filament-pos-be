<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::all();
        return response()->json([
            'data' => $product,
            'message' => 'Products retrieved successfully',
            'success' => true,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
        ]);
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }

    public function showByBarcode($barcode) {
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
                'success' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => $product,
            'message' => 'Product retrieved successfully',
            'success' => true,
        ], 200);
    }
}
