<?php

namespace App\Exceptions;

use Exception;

class NotAuthorizedException extends Exception
{
    public function report()
    {
        \Log::info("Not Authorized Exception Called!");
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $this->getMessage(),
        ], 403);
    }
}
