<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Customer\StoreRequest;
use App\Http\Requests\V1\Customer\UpdateRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    )
    {
        //
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->customerService->setMerchantIdFromRequest($request)->getPaginate($request);

        return $this->customerService->getJsonResponse();
    }

    public function getAll(Request $request)
    {
        $this->customerService->setMerchantIdFromRequest($request)->getAll($request);

        return $this->customerService->getJsonResponse();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $this->customerService->store($request);

        return $this->customerService->getJsonResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->customerService->detail($id);

        return $this->customerService->getJsonResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->customerService->update($id, $request);

        return $this->customerService->getJsonResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->customerService->delete($id);

        return $this->customerService->getJsonResponse();
    }
}
