<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSupplierReturnRequest;
use App\Http\Requests\Api\V1\UpdateSupplierReturnRequest;
use App\Http\Resources\Api\V1\SupplierReturnResource;
use App\Models\SupplierReturn;
use App\Services\SupplierReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierReturnController extends Controller
{
    public function __construct(
        private SupplierReturnService $supplierReturnService
    ) {}

    public function index(Request $request)
    {
        $returns = SupplierReturn::query()
            ->with(['supplier', 'purchase', 'items.product', 'creator'])
            ->latest()
            ->paginate(15);

        return SupplierReturnResource::collection($returns);
    }

    public function store(
        StoreSupplierReturnRequest $request
    ): SupplierReturnResource {
        $return = $this->supplierReturnService->createDraft(
            $request->validated(),
            $request->user()
        );

        return new SupplierReturnResource(
            $return->load(['supplier', 'purchase', 'items.product', 'creator'])
        );
    }

    public function show(SupplierReturn $supplierReturn): SupplierReturnResource
    {
        $supplierReturn->load([
            'supplier',
            'purchase',
            'items.product',
            'creator',
        ]);

        return new SupplierReturnResource($supplierReturn);
    }

    public function update(
        UpdateSupplierReturnRequest $request,
        SupplierReturn $supplierReturn
    ): SupplierReturnResource {
        $return = $this->supplierReturnService->updateDraft(
            $supplierReturn,
            $request->validated()
        );

        return new SupplierReturnResource($return);
    }

    public function validateReturn(
        Request $request,
        SupplierReturn $supplierReturn
    ): SupplierReturnResource {
        $return = $this->supplierReturnService->validateReturn(
            $supplierReturn,
            $request->user()
        );

        return new SupplierReturnResource($return);
    }

    public function cancel(
        SupplierReturn $supplierReturn
    ): SupplierReturnResource {
        $return = $this->supplierReturnService->cancelDraft(
            $supplierReturn
        );

        return new SupplierReturnResource($return);
    }
}
