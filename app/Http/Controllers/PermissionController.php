<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        // الطريقة القديمة (تسبب الخطأ)
        // $this->middleware('permission:edit roles|create roles');

        // الطريقة الصحيحة والمباشرة
        // نستخدم authorize() للتحقق من الصلاحية مباشرة داخل الدالة
        // أو يمكننا استخدام middleware بطريقة مختلفة إذا أردنا
    }

    public function index()
    {
        // الحل الأفضل: التحقق من الصلاحية هنا مباشرة
        // هذا يضمن أن المستخدم لديه إحدى الصلاحيتين
        if (!auth()->user()->hasAnyPermission(['edit roles', 'create roles'])) {
            abort(403, 'This action is unauthorized.');
        }

        // إرجاع الصلاحيات مجمعة حسب الجزء الأول من الاسم (قبل المسافة)
        return Permission::all()->groupBy(function ($permission) {
            // استخراج الجزء الأول من اسم الصلاحية ليكون مفتاح المجموعة
            $parts = explode(' ', $permission->name);
            // إذا كانت الصلاحية من كلمة واحدة (مثل catalog)، استخدمها كما هي
            return count($parts) > 1 ? $parts[1] : $parts[0];
        });
    }
}
