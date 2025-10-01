<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogItemRequest; // <-- 1. استيراد
use App\Http\Requests\UpdateCatalogItemRequest; // <-- 2. استيراد
use App\Models\CatalogItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CatalogItemController extends Controller
{

      public function __construct()
    {
        // هذا السطر يربط المتحكم بسياسة الصلاحيات الخاصة به
        // ويتطلب وجود CatalogItemPolicy
        $this->authorizeResource(CatalogItem::class, 'catalog_item');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResource
    {
        // يمكن إضافة فلترة هنا، مثلاً لجلب الأصناف النشطة فقط
        $items = CatalogItem::where('is_active', true)->latest()->paginate(20);
        return JsonResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCatalogItemRequest $request): JsonResponse // <-- 3. استخدام Store...
    {
        $item = CatalogItem::create($request->validated());

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CatalogItem $catalogItem): JsonResource
    {
        return new JsonResource($catalogItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCatalogItemRequest $request, CatalogItem $catalogItem): JsonResource // <-- 4. استخدام Update...
    {
        $catalogItem->update($request->validated());

        return new JsonResource($catalogItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CatalogItem $catalogItem): Response
    {
        $catalogItem->delete(); // Soft delete

        return response()->noContent();
    }
}
