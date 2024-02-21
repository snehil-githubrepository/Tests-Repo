<?php

namespace App\Exceptions;

use Exception;

class ControllerException extends Exception
{
    public function report()
    {
        // You can log the exception here or send it to a logging service
        \Log::info("Controller Exception Called!");
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => 'Controller Exception',
            'message' => $this->getMessage(),
        ], 512);
    }
}
