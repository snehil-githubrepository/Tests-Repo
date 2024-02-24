<?php

namespace App\Exceptions;

use Exception;

class TestException extends Exception
{
    public function report()
    {
        \Log::info("Test Exception Called!");
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => 'Test Exception',
            'message' => $this->getMessage(),
        ], 401);
    }   
}
