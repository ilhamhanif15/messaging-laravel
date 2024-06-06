<?php

namespace App\Traits\Requests;

use Illuminate\Http\Request;

trait MerchantIdReq
{
    protected $merchantId;

    public function setMerchantIdFromRequest(Request $request): self
    {
        $this->merchantId = $request->merchant_id;
        return $this;
    }

    public function requireMerchantId(): self
    {
        if (!$this->merchantId) {
            abort(response()->json(['message' => 'Require merchant id'], 422));
        }

        return $this;
    }

    public function getMerchantId(): ?string
    {
        return $this->merchantId;
    }
}
