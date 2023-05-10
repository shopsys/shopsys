<?php

declare(strict_types=1);

namespace App\Model\GoPay\Exception;

use Exception;

class GoPayPaymentDownloadException extends Exception implements GoPayException
{
    /**
     * @param mixed $url
     * @param mixed $method
     * @param mixed|null $requestData
     * @param mixed|null $responseData
     * @param mixed|null $expectedCode
     * @param mixed|null $actualCode
     */
    public function __construct(
        $url,
        $method,
        $requestData = null,
        $responseData = null,
        $expectedCode = null,
        $actualCode = null
    ) {
        parent::__construct(
            sprintf(
                "Unexpected response code: %s\n"
                . "Expected code: %s\n"
                . "URL: %s\n"
                . "Method: %s\n"
                . "Request data:\n%s\n"
                . "Response data:\n%s\n",
                $actualCode,
                $expectedCode,
                $url,
                $method,
                print_r($requestData, true),
                print_r($responseData, true)
            )
        );
    }
}
