<?php

namespace App\Services;

use App\Http\Requests\V1\TemplateMessage\StoreRequest;
use App\Http\Requests\V1\TemplateMessage\UpdateRequest;
use App\Models\TemplateMessage;
use App\Traits\JsonResponseable;
use App\Traits\Requests\MerchantIdReq;
use App\Utils\PaginationableUtil;
use App\Utils\TextParserUtil;
use App\Utils\TryCatchUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TemplateMessageService
{
    use JsonResponseable, MerchantIdReq;

    public function getPaginate(Request $request): LengthAwarePaginator
    {
        $this->requireMerchantId();

        $paginationableUtil = (new PaginationableUtil())
            ->setModel(new TemplateMessage())
            ->setSearchableColumns([
                'title',
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

        $data = TemplateMessage::whereMerchantId($this->getMerchantId())->get();

        $this->setJsonResponse(
            response()->json($data, 200)
        );

        return $data;
    }

    public function detail($id): TemplateMessage
    {
        $templateMessage = TemplateMessage::findOrFail($id);

        $this->setJsonResponse(
            response()->json($templateMessage, 200)
        );

        return $templateMessage;
    }

    public function parseRequest($request): array
    {
        $data = $request->validated();
        $data['body'] = TextParserUtil::convertTrailingBreaks($data['body']);

        return $data;
    }

    public function store(StoreRequest $request): TemplateMessage
    {
        return TryCatchUtil::DBTransaction(function() use($request) {
            $templateMessage = TemplateMessage::create($this->parseRequest($request));

            $this->setJsonResponse(
                response()->json($templateMessage, 200)
            );

            return $templateMessage;
        });
    }

    public function update($id, UpdateRequest $request): TemplateMessage
    {
        $templateMessage = TemplateMessage::findOrFail($id);

        return TryCatchUtil::DBTransaction(function() use($templateMessage, $request) {
            $templateMessage->fill($this->parseRequest($request))->save();

            $this->setJsonResponse(
                response()->json($templateMessage, 200)
            );

            return $templateMessage;
        });
    }

    public function delete($id): TemplateMessage
    {
        $templateMessage = TemplateMessage::findOrFail($id);

        return TryCatchUtil::DBTransaction(function() use($templateMessage) {
            $templateMessage->delete();

            $this->setJsonResponse(
                response()->json(['message' => 'Delete success.'], 200)
            );

            return $templateMessage;
        });
    }
}