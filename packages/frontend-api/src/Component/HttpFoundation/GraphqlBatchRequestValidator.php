<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GraphqlBatchRequestValidator
{
    protected const string CONTENT_TYPE_FORM_DATA = 'multipart/form-data';

    protected const string CONTENT_TYPE_JSON = 'application/json';

    public function __construct(
        protected readonly int $maxOperations,
    ) {
    }

    public function getLimitViolationResponse(Request $request): ?JsonResponse
    {
        $operationsCount = $this->getOperationsCount($request);

        if ($operationsCount === null || $operationsCount <= $this->maxOperations) {
            return null;
        }

        return new JsonResponse([
            'errors' => [
                [
                    'message' => sprintf('Batch request cannot contain more than %d operations.', $this->maxOperations),
                ],
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function getOperationsCount(Request $request): ?int
    {
        $contentType = explode(';', (string)$request->headers->get('content-type'), 2)[0];

        if ($contentType === static::CONTENT_TYPE_JSON) {
            return $this->getJsonOperationsCount($request);
        }

        if ($contentType === static::CONTENT_TYPE_FORM_DATA) {
            return $this->getFormDataOperationsCount($request);
        }

        return null;
    }

    protected function getJsonOperationsCount(Request $request): ?int
    {
        try {
            $requestData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return $this->getListCount($requestData);
    }

    protected function getFormDataOperationsCount(Request $request): ?int
    {
        $requestData = $request->request->all();

        if (isset($requestData['operations']) && is_string($requestData['operations'])) {
            try {
                $requestData = json_decode($requestData['operations'], true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return null;
            }
        } elseif (isset($requestData['operations']) && is_array($requestData['operations'])) {
            $requestData = $requestData['operations'];
        }

        return $this->getListCount($requestData);
    }

    protected function getListCount(mixed $requestData): ?int
    {
        if (!is_array($requestData) || !array_is_list($requestData)) {
            return null;
        }

        return count($requestData);
    }
}
