<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ControllerException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    // Helper method to generate session ID
    private function generateSessionId()
    {
        return md5(uniqid());
    }

    // Helper method to set session ID for the user
    private function setSessionId($user, $sessionId)
    {
        $user->session_id = $sessionId;
        $user->save();
    }

    public function register(Request $request) {
        //register logic - done

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'resource_type' => 'required|string|in:customer,admin',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'resource_type' => $validatedData['resource_type'],
        ]);

        // Log in the newly registered user
        Auth::login($user);

        // Generate or fetch session ID
        $sessionId = $this->generateSessionId();
        $this->setSessionId($user, $sessionId);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ])->cookie('session_id', $sessionId)->status(200); 
    }

    public function login(Request $request) {
        //login logic
        try {
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $sessionId = $this->generateSessionId();
                
                // Store the session ID in the user record
                $this->setSessionId($user, $sessionId);
            
                // Set session ID in a secure and HttpOnly cookie
                $response = redirect()->intended('home');
                $response->cookie('session_id', $sessionId, 0, '/', null, true, true); // Secure and HttpOnly
                
                return $response;
            } else {
                // Return a specific error message for invalid credentials
                return response()->json(["message"=> "Invalid email or password"], 403);
            }
        
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 512);
        }
    }

    public function logout(Request $request) {
        //logout logic - done
        if(Auth::check()){ // If logged in
            $user = auth()->user();
            $user->session_id = null;
            $user->save();
            auth()->logout();
        }
    
        return redirect('/login');
    }

    public function showProfile($id) {
        //show Profile of User
        $user = User::find($id);
        return response()->json([
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request) {
        //update profile logic
        $validatedData = $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();

        try{
            $user = User::find($validatedData['id']);

            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = bcrypt($validatedData['password']); // Hash the password
            
            $user->save();

            DB::commit();

            return response()->json(['message' => 'Successfully Updated', 'id' => $user->id], 200);

        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to update profile'], 500);
        }

    }
    
    public function deleteProfile(User $user, $id) {
        //delete profile logic
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            if ($user->resource_type == 'customer' || $user->resource_type == 'admin') {
                $user->delete();
            } 
            
            DB::commit();

            return response('Deleted', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to delete profile'], 500);
        }
    }
}
