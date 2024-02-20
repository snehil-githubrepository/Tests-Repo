<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AccessControlMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // return redirect()->route('login'); 
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        // For customers, restrict access to specific routes (e.g., product creation)
        if ($request->route()->getName() === 'products.store') {
            // If it's a request to store a product, allow access
            return $next($request);
        } else {
            // return redirect()->route('home'); // Example redirect to home page
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }
}
