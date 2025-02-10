<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function verifyProduct($id) {
        $product = Product::findOrFail($id);

        if(!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->update(['status' => 'verified']);

        return response()->json([
            'status' => true,
            'message' => 'Product successfully verified',
            'data' => new ProductResource($product)
        ]);
    }

}
