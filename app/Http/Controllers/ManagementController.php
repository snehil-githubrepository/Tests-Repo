<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Prod;
use App\Http\Resources\ProductResource;
use app\Http\Requests\ProductRequest;

class ManagementController extends Controller
{
    
    public function storeProduct(Request $request)
    {
        try {
            $request->validate([
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'product_description' => 'required|string',
            ],
            [
                'product_name.required' => 'The product name is required.',
                'product_name.string' => 'The product name must be a string.',
                'product_price.required' => 'The product price is required.',
                'product_price.numeric' => 'The product price must be a number.',
                'product_description.required' => 'The product description is required.',
                'product_description.string' => 'The product description must be a string.',
            ]
        );

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
        } catch (\Exception $e) {
            \Log::error('Error creating product: ' . $e->getMessage());
            
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 422);
        }
    }


    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
    
            $request->validate([
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'product_description' => 'required|string',
            ], 
            [
                'product_name.required' => 'The product name is required.',
                'product_name.string' => 'The product name must be a string.',
                'product_price.required' => 'The product price is required.',
                'product_price.numeric' => 'The product price must be a number.',
                'product_description.required' => 'The product description is required.',
                'product_description.string' => 'The product description must be a string.',
            ]
        );
    
            $product->update([
                'product_name' => $request->product_name,
                'product_price' => $request->product_price,
                'product_description' => $request->product_description,
            ]);
    
            return response()->json(['message' => 'Product updated successfully', 'product' => new ProductResource($product)])->setStatusCode(200);
        } catch (\Exception $e) {
            \Log::error('Error updating product: ' . $e->getMessage());
            
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }
    

    //single
    public function showProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            return new ProductResource($product);
        } catch (\Exception $e) {
            \Log::error('Error retrieving product: ' . $e->getMessage());
            
            return response()->json(['message' => 'Product not found', 'error' => $e->getMessage()], 404);
        }
    }
    
    public function showAllProducts()
    {
        try {
            $products = Product::all();
            return ProductResource::collection($products);
        } catch (\Exception $e) {
            \Log::error('Error retrieving all products: ' . $e->getMessage());
            
            return response()->json(['message' => 'Failed to retrieve products', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            $productId = $product->id;
            $product->delete();
            return response()->json([
                'message' => 'Product deleted successfully',
                'deleted_product_id' => $productId 
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting product: ' . $e->getMessage());
            
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
        }
    }


    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string',
            ], [
                'query.required' => 'The product name is required.',
                'query.string' => 'The product name must be a string.'
            ]
        );

            $query = $request->query('query');

            $products = Product::where('product_name', 'like', "%{$query}%")
                                ->orWhere('product_description', 'like', "%{$query}%")
                                ->get();

            return ProductResource::collection($products);
        } catch (\Exception $e) {
            \Log::error('Error searching products: ' . $e->getMessage());
            
            return response()->json(['message' => 'Failed to search products', 'error' => $e->getMessage()], 500);
        }
    }

}
