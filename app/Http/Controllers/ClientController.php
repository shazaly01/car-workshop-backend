<?php

namespace App\Http\Controllers;

// الكلاسات التي أنشأناها
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource; // <-- استيراد المورد
use App\Models\Client;

// كلاسات Laravel
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{

    use AuthorizesRequests; // <-- 2. استخدام

    /**
     * --- 3. إضافة الـ Constructor لتفعيل الـ Policy ---
     */
    public function __construct()
    {
        // هذا السطر هو الذي يربط المتحكم بالـ Policy
        $this->authorizeResource(Client::class, 'client');
    }
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
   public function index()
{
    // استخدم with() للتحميل المسبق للعلاقات التي تحتاجها في القائمة
    $clients = Client::withCount(['vehicles', 'workOrders']) // مثال: جلب عدد السيارات وأوامر العمل
                       ->latest()
                       ->paginate(15);

    return ClientResource::collection($clients);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): ClientResource
    {
        // إنشاء العميل من البيانات التي تم التحقق منها
        $client = Client::create($request->validated());

        // إرجاع العميل الجديد باستخدام المورد لتنسيق الـ JSON
        return new ClientResource($client);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): ClientResource
    {
        // تحميل العلاقات اللازمة
        $client->load('vehicles')->loadCount('workOrders');

        // إرجاع العميل باستخدام المورد
        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        // تحديث بيانات العميل بالبيانات التي تم التحقق منها
        $client->update($request->validated());

        // إرجاع العميل بعد التحديث باستخدام المورد
        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): Response
    {
        // تنفيذ الحذف الناعم (Soft Delete)
        $client->delete();

        // إرجاع استجابة فارغة مع كود الحالة 204 No Content
        return response()->noContent();
    }
}
