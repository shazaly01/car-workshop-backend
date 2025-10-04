<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request; // <-- إضافة
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; // <-- إضافة

class RoleController extends Controller
{
    // ... (دوال index, store, show لا تتغير) ...
    public function index()
    {
        $this->authorize('viewAny', Role::class);
        return RoleResource::collection(Role::with('permissions')->get());
    }

    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create', Role::class);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }
        return new RoleResource($role->load('permissions'));
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);
        return new RoleResource($role->load('permissions'));
    }

    /**
     * تحديث اسم الدور فقط.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        // التحقق من صحة الاسم فقط
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->update(['name' => $validated['name']]);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * دالة جديدة لتحديث صلاحيات الدور فقط.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);

        return new RoleResource($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);
        $role->delete();
        return response()->noContent();
    }
}
