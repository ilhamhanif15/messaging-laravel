<?php

namespace App\Services;

use App\Http\Requests\V1\Customer\StoreRequest;
use App\Http\Requests\V1\Customer\UpdateRequest;
use App\Models\Customer;
use App\Traits\JsonResponseable;
use App\Traits\Requests\MerchantIdReq;
use App\Utils\PaginationableUtil;
use App\Utils\TryCatchUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    use JsonResponseable, MerchantIdReq;

    public function getPaginate(Request $request): LengthAwarePaginator
    {
        $this->requireMerchantId();

        $paginationableUtil = (new PaginationableUtil())
            ->setModel(new Customer())
            ->setSearchableColumns([
                'name',
                'email',
                'phone_number',
            ])
            ->setFilterableCols([
                'is_active',
            ])
            ->extendQuery(fn ($q) => $q->whereMerchantId($this->merchantId))
            ->setRequest($request);
        
        $dataPaginate = $paginationableUtil->paginate();

        $this->setJsonResponse(
            response()->json($dataPaginate, 200)
        );

        return $dataPaginate;
    }

    public function getAll(Request $request): Collection
    {
        $this->requireMerchantId();

        $data = Customer::whereMerchantId($this->merchantId)->get();

        $this->setJsonResponse(
            response()->json($data, 200)
        );

        return $data;
    }

    public function detail($customerId): Customer
    {
        $customer = Customer::findOrFail($customerId);

        $this->setJsonResponse(
            response()->json($customer, 200)
        );

        return $customer;
    }

    public function store(StoreRequest $request): Customer
    {
        return TryCatchUtil::DBTransaction(function() use($request) {
            $customer = Customer::create($request->validated());

            $this->setJsonResponse(
                response()->json($customer, 200)
            );

            return $customer;
        });
    }

    public function update($customerId, UpdateRequest $request): Customer
    {
        $customer = Customer::findOrFail($customerId);

        return TryCatchUtil::DBTransaction(function() use($customer, $request) {
            $customer->fill($request->validated())->save();

            $this->setJsonResponse(
                response()->json($customer, 200)
            );

            return $customer;
        });
    }

    public function delete($customerId): Customer
    {
        $customer = Customer::findOrFail($customerId);

        return TryCatchUtil::DBTransaction(function() use($customer) {
            $customer->delete();

            $this->setJsonResponse(
                response()->json(['message' => 'Delete success.'], 200)
            );

            return $customer;
        });
    }
}