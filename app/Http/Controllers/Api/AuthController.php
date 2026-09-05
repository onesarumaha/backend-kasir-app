<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\Tenant;
class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $result = DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name'      => $request->store_name,
                'slug'      => Str::slug($request->store_name) . '-' . Str::random(5),
                'is_active' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'admin',
            ]);

            $token = $user->createToken('kasir-token')->plainTextToken;

            return [
                'user'  => $user->load('tenant'),
                'token' => $token,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => $result,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        // Jika sampai di sini, artinya email, password, dan status tenant SUDAH VALID
        $user = User::where('email', $request->email)->first();

        $user->tokens()->delete();

        $token = $user->createToken('kasir-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => new UserResource($user->load('tenant')),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
