<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\Exception;

use Exception;
use GoPay\Http\Response;

class GoPayPaymentDownloadException extends Exception
{
    public function __construct(
        public readonly string $url,
        public readonly string $method,
        public readonly int $expectedCode,
        public readonly ?array $requestData,
        public readonly ?Response $responseData,
    ) {
        parent::__construct('Unexpected response code');
    }

    /**
     * @return array<string, mixed>
     */
    public function getLogContext(): array
    {
        return [
            'exception' => $this,
            'url' => $this->url,
            'method' => $this->method,
            'expectedCode' => $this->expectedCode,
            'responseCode' => $this->responseData?->statusCode,
            'responseBody' => $this->responseData?->rawBody,
            'requestData' => $this->requestData,
        ];
    }
}
