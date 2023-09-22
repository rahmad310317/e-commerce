<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {

            // Validate request
            $request->validate([
                'email' => 'requied|email',
                'password' =>  'required',
            ]);

            // Find user by email
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            // Cek Password
            $user = User::where('email', 'password')->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Password', 401);
            }

            // Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Succes');
        } catch (Exception $error) {
            // return response error
            return ResponseFormatter::error('Authentication Failed', 400);
        }
    }

    public function register(Request $request)
    {
        try {

            // Validate request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            // Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Return  response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (Exception $error) {
            // return response error
            return ResponseFormatter::error('Authentication Failed', 400);
        }
    }

    public function logout(Request $request)
    {
        try {

            // Revoke token
            $token = $request->user()->currentAccessToken()->delete();

            //return response
            return ResponseFormatter::success('Log out Success', 200);
        } catch (Exception $error) {
            // return response error
            return ResponseFormatter::error('Authentication Failed', 400);
        }
    }

    public function fecth(Request $request)
    {
        try {
            // Get user
            $user = $request->user();

            // return response
            return ResponseFormatter::success('Fetch Success', 200);
        } catch (Exception $error) {
            // return response error
            return ResponseFormatter::error('Authentication Failed', 400);
        }
    }
}
