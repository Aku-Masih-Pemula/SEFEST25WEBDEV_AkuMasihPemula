<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = Auth::user();

        if ($request->route('id')) {
            $product = Product::find($request->route('id'));

            if (!$product || $product->seller_id !== $currentUser->id) {
                return response()->json(['message' => 'Invalid account'], 403);
            }
        }

        return $next($request);
    }
}
