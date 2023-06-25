<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                "name" => "required|string|max:255",
                "email" => "required|string|email|max:255|unique:users",
                "password" => "required|string|min:6|confirmed",
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                422
            );
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        return response()->json(
            [
                "message" => "User registered successfully",
                "user" => $user,
            ],
            201
        );
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                "email" => "required|string|email",
                "password" => "required|string",
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    "message" => "Validation failed",
                    "errors" => $e->errors(),
                ],
                422
            );
        }

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                "email" => ["Incorrect email or password"],
            ]);
        }

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "access_token" => $token,
            "token_type" => "Bearer",
            "user" => $user,
        ]);
    }
}
