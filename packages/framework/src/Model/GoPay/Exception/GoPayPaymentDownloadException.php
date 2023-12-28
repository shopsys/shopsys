<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\Exception;

use Exception;
use GoPay\Http\Response;

class GoPayPaymentDownloadException extends Exception
{
    /**
     * @param string $url
     * @param string $method
     * @param int $expectedCode
     * @param array|null $requestData
     * @param \GoPay\Http\Response|null $responseData
     */
    public function __construct(
        public readonly string $url,
        public readonly string $method,
        public readonly int $expectedCode,
        public readonly ?array $requestData,
        public readonly ?Response $responseData,
    ) {
        parent::__construct('Unexpected response code');
    }
}
