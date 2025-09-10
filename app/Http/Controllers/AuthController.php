<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    function Register(Request $R)
    {
        try {
            $R->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $cred = new User();
            $cred->name = $R->name;
            $cred->email = $R->email;
            $cred->password = Hash::make($R->password);
            $cred->save();

            $response = [
                'status' => 200,
                'message' => 'Registration successful! Welcome to our community.'
            ];
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    function Login(Request $R)
    {
        $R->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $R->email)->first();

        if ($user && Hash::check($R->password, $user->password)) {
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            $response = [
                'status' => 200,
                'token' => $token,
                'user' => $user,
                'message' => 'Successfully logged in! Welcome back.'
            ];
            return response()->json($response);
        } elseif (!$user) {
            $response = [
                'status' => 404,
                'message' => 'No account found with this email.'
            ];
            return response()->json($response, 404);
        } else {
            $response = [
                'status' => 401,
                'message' => 'Wrong email or password! Please try again.'
            ];
            return response()->json($response, 401);
        }
    }


    public function getallusers(): \Illuminate\Http\JsonResponse
    {
        try {
            $users = DB::table('users')->get();
            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Error in getallusers: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching users'], 500);
        }
    }

}
