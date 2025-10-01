<?php

namespace App\Http\Controllers;

// الكلاسات التي أنشأناها
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

// كلاسات Laravel
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VehicleController extends Controller
{

    use AuthorizesRequests;

    public function __construct()
    {
        // هذا السطر يفعل الـ Policy لكل دوال المتحكم
        $this->authorizeResource(Vehicle::class, 'vehicle');
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
{
    $query = Vehicle::with('client')->latest();

    // فلترة حسب العميل
    if ($request->has('client_id')) {
        $query->where('client_id', $request->query('client_id'));
    }

    $vehicles = $query->paginate(20);
    return VehicleResource::collection($vehicles);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request): VehicleResource
    {
        $vehicle = Vehicle::create($request->validated());

        return new VehicleResource($vehicle);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): VehicleResource
    {
        // تحميل علاقة العميل المالك لضمان ظهورها في الـ JSON
        $vehicle->loadMissing('client');

        return new VehicleResource($vehicle);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): VehicleResource
    {
        $vehicle->update($request->validated());

        // تحميل العلاقة مرة أخرى في حال تغيرت (وإن كان غير محتمل هنا)
        $vehicle->loadMissing('client');

        return new VehicleResource($vehicle);
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Vehicle $vehicle): Response | JsonResponse
{
    // تحقق مما إذا كانت السيارة مرتبطة بأي أمر عمل
    if ($vehicle->workOrders()->exists()) {
        return response()->json([
            'message' => 'لا يمكن حذف هذه السيارة لأنها مرتبطة بأوامر عمل سابقة.'
        ], 409); // 409 Conflict
    }

    $vehicle->delete();
    return response()->noContent();
}

}
