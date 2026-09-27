<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => Str::slug($data['company_name']),
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole('owner');

            $token = $user->createToken('api-token')->plainTextToken;

            return [
                'tenant' => $tenant,
                'user' => $user->load('tenant'),
                'token' => $token,
            ];
        });

        return response()->json([
            'message' => 'Inscription réussie.',
            'data' => $result,
        ], 201);
    }
}
