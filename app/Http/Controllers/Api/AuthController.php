<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Вход по логину, как и на сайте.
     *
     * Токен привязан к названию устройства: с телефона и планшета выходят
     * по отдельности, и потеря одного не выкидывает из второго.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device' => ['required', 'string', 'max:60'],
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => __('Логин или пароль не подходят'),
            ]);
        }

        // Токен с прошлого входа с того же устройства отзывается: иначе список
        // токенов растёт при каждой переустановке приложения.
        $user->tokens()->where('name', $data['device'])->delete();

        return response()->json([
            'token' => $user->createToken($data['device'])->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => new UserResource($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true]);
    }
}
