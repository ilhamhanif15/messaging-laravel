<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TemplateMessage\StoreRequest;
use App\Http\Requests\V1\TemplateMessage\UpdateRequest;
use App\Services\TemplateMessageService;
use Illuminate\Http\Request;

class TemplateMessageController extends Controller
{
    public function __construct(
        private TemplateMessageService $templateMessageService
    )
    {
        //
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->templateMessageService->setMerchantIdFromRequest($request)->getPaginate($request);

        return $this->templateMessageService->getJsonResponse();
    }

    public function getAll(Request $request)
    {
        $this->templateMessageService->setMerchantIdFromRequest($request)->getAll($request);

        return $this->templateMessageService->getJsonResponse();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $this->templateMessageService->store($request);

        return $this->templateMessageService->getJsonResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->templateMessageService->detail($id);

        return $this->templateMessageService->getJsonResponse();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->templateMessageService->update($id, $request);

        return $this->templateMessageService->getJsonResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->templateMessageService->delete($id);

        return $this->templateMessageService->getJsonResponse();
    }
}
