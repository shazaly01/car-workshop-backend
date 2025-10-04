<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // --- تمت إزالة الـ __construct بالكامل ---

    public function index()
    {
        // استخدام الـ Policy للتحقق من الصلاحية
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->paginate(15);
        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        // استخدام الـ Policy للتحقق من الصلاحية
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users', // إضافة حقل اسم المستخدم
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findById($request->role_id, 'api'); // تحديد الـ guard مهم
        $user->assignRole($role);

        // تحميل الصلاحيات والأدوار لإرجاعها في الـ Resource
        return new UserResource($user->load('roles', 'permissions'));
    }

    public function show(User $user)
    {
        // استخدام الـ Policy للتحقق من الصلاحية
        $this->authorize('view', $user);

        // تحميل الصلاحيات والأدوار لإرجاعها في الـ Resource
        return new UserResource($user->load('roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        // استخدام الـ Policy للتحقق من الصلاحية
        $this->authorize('update', $user);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id, // إضافة حقل اسم المستخدم
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update($request->only('name', 'username', 'email'));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $role = Role::findById($request->role_id, 'api'); // تحديد الـ guard مهم
        $user->syncRoles($role);

        // تحميل الصلاحيات والأدوار لإرجاعها في الـ Resource
        return new UserResource($user->load('roles', 'permissions'));
    }

    public function destroy(User $user)
    {
        // استخدام الـ Policy للتحقق من الصلاحية (منطق الحماية تم نقله إلى الـ Policy)
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }
}
