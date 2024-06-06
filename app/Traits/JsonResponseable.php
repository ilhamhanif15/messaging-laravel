<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait JsonResponseable
{
    /**
     * @var JsonResponse
     */
    protected $currentJsonResponse;

    /**
     * @param JsonResponse $jsonResponse
     * @return self
     */
    public function setJsonResponse(JsonResponse $jsonResponse)
    {
        $this->currentJsonResponse = $jsonResponse;

        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function getJsonResponse()
    {
        return $this->currentJsonResponse;
    }
}
