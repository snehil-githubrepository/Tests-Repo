<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Prod;
use App\Http\Resources\ProductResource;

class ManagementController extends Controller
{
    
    public function storeProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'product_description' => 'required|string',
        ]);

        $user_id = auth()->id();

        $product = Product::create([
            'user_id' => $user_id,
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_description' => $request->product_description,
        ]);

        //example Table B
        // $prod = Prod::create([
        //     'product_id' => $product->id,
        //     'product_name' => $request->product_name,
        //     'product_price' => $request->product_price,
        //     'product_description' => $request->product_description,
        // ]);

        return response()->json(['message' => 'Product created successfully', 'product' => new ProductResource($product)], 201);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'product_description' => 'required|string',
        ]);

        $product->update([
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_description' => $request->product_description,
            // Add other fields if necessary
        ]);

        return response()->json(['message' => 'Product updated successfully', 'product' => new ProductResource($product)])->setStatusCode(200);
    }

    //single
    public function showProduct($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }

    public function showAllProducts()
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $productId = $product->id;
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
            'deleted_product_id' => $productId 
        ], 200);
    }

    public function searchProducts(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->query('query');

        $products = Product::where('product_name', 'like', "%{$query}%")
                            ->orWhere('product_description', 'like', "%{$query}%")
                            ->get();

        return ProductResource::collection($products);
    }
}
