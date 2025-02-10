<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::with(['seller', 'images'])
            ->where('status', 'verified')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return ProductResource::collection($product)->additional([
            'status' => true,
            'message' => 'All products retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|string',
            'description' => 'required',
            'price' => 'required|decimal:2',
            'stock' => 'required|integer',
            'category_id' => 'required',
            'images.*' => 'nullable|file|max:5000|mimes:png,jpg,jpeg',
        ]);

        $user = Auth::user();

        $product = Product::create([
            'seller_id' => $user->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],

            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'status' => 'banned'
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('product_images', $filename, 'public');

                $product->images()->create([
                    'path' => $path
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Product created Successfully',
            'data' => new ProductResource($product->load(['seller', 'images']))
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['seller', 'images'])
            ->where('id', $id)
            ->where('status', 'unbanned')
            ->firstOrFail();

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product retrieved Successfully',
            'data' => new ProductResource($product)
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|max:255|string',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required',
            'images.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5000',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:product_images,id',
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
        ]);

        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $image->path));
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('product_images', $filename, 'public');

                $product->images()->create(['path' => 'storage/' . $path]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->load(['seller', 'images']))
        ]);
    }

    public function destroy($id)
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        foreach ($product->images as $image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $product->images()->delete();

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
