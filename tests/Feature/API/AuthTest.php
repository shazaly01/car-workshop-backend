<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // هذا الـ Trait يقوم بإعادة تهيئة قاعدة البيانات قبل كل اختبار
    // لضمان أن كل اختبار يبدأ على قاعدة بيانات نظيفة
    use RefreshDatabase;

    /**
     * الاختبار الأول: اختبار تسجيل الدخول الناجح.
     * @test
     */
    public function user_can_login_with_correct_credentials(): void
    {
        // 1. تجهيز المسرح: إنشاء مستخدم
        $user = User::factory()->create([
            'password' => bcrypt('password123'), // نستخدم كلمة مرور معروفة
        ]);

        // 2. الفعل: إرسال طلب تسجيل الدخول
        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);

        // 3. التحقق:
        $response->assertStatus(200); // هل الاستجابة ناجحة؟
        $response->assertJsonStructure(['token']); // هل تحتوي الاستجابة على مفتاح 'token'؟

        // تحقق إضافي: هل الـ token يعمل؟
        $token = $response->json('token');
        $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)->assertJson(['email' => $user->email]);
    }

    /**
     * الاختبار الثاني: اختبار تسجيل الدخول الفاشل.
     * @test
     */
    public function user_cannot_login_with_incorrect_credentials(): void
    {
        // 1. تجهيز المسرح: إنشاء مستخدم
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // 2. الفعل: إرسال طلب بكلمة مرور خاطئة
        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        // 3. التحقق:
        $response->assertStatus(422); // هل الاستجابة هي خطأ في البيانات؟
        $response->assertJsonValidationErrorFor('username'); // هل رسالة الخطأ مرتبطة بحقل 'username'؟
    }

    /**
     * الاختبار الثالث: اختبار جلب بيانات المستخدم المسجل.
     * @test
     */
    public function authenticated_user_can_fetch_their_data(): void
    {
        // 1. تجهيز المسرح: إنشاء مستخدم
        $user = User::factory()->create();

        // 2. الفعل: التصرف كمستخدم مسجل وإرسال طلب
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

        // 3. التحقق:
        $response->assertStatus(200); // هل الاستجابة ناجحة؟
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]); // هل تحتوي على بيانات المستخدم الصحيحة؟
    }
}
