<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource; // <-- استيراد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            throw ValidationException::withMessages([
                'username' => [trans('auth.failed')],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- التعديل هنا: تحميل العلاقات قبل استخدام الـ Resource ---
        $user->load(['roles', 'permissions']);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user), // <-- استخدام الـ Resource
            'token' => $token,
        ]);
    }
}
