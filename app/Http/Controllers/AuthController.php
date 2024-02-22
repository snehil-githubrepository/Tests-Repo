<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ControllerException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\UserResource;
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

        $cookie = cookie('session_id', $sessionId);

        return (new UserResource($user))->response()->withCookie($cookie)->setStatusCode(200);
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
                $this->setSessionId($user, $sessionId);
            
                // Set session ID in a cookie
                $cookie = cookie('session_id', $sessionId);

                return (new UserResource($user))->response()->withCookie($cookie)->setStatusCode(302); //re-direction
            } else {
                throw ValidationException::withMessages(['email' => 'Invalid email or password']);
            }
            // else {
            //     // Return a specific error message for invalid credentials
            //     return response()->json(["message"=> "Invalid email or password"], 403);
            // }
        
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
    
        return response()->json(['error' => 'User is not logged in'])
        ->withHeaders(['Location' => '/login'])
        ->setStatusCode(302);;
    }

    public function showProfile(Request $request, $id) {
            if (Auth::check()) {
                $user = Auth::user();
        
                if ($user->id == $id) {
                    return (new UserResource($user))->response()->setStatusCode(200);
                } else {
                    // Return a 403 Forbidden response if the user is not authorized to access the requested profile
                    return response()->json(['error' => 'Forbidden'], 403);
                }
            } else {
                // Return a 401 Unauthorized response if the user is not authenticated
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    }

    public function updateProfile(Request $request, $id) {
        //update profile logic
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'password' => 'sometimes|string|min:6',
        ]);
    
        DB::beginTransaction();
    
        try {
            $user = User::findOrFail($id);
    
            // Update user attributes only if provided in the request
            if (isset($validatedData['name'])) {
                $user->name = $validatedData['name'];
            }
    
            if (isset($validatedData['email'])) {
                $user->email = $validatedData['email'];
            }
    
            if (isset($validatedData['password'])) {
                $user->password = bcrypt($validatedData['password']);
            }
    
            $user->save();
    
            DB::commit();
    
            return response()->json(['message' => 'Successfully Updated', 'id' => $user->id], 200);
        } catch (\Exception $e) {
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

            return response()->json(['message' => 'Profile deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to delete profile'], 500);
        }
    }
}
