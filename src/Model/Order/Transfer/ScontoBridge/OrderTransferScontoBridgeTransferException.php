<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\TransferException;

class OrderTransferScontoBridgeTransferException extends TransferException
{
    /**
     * @var int
     */
    private int $httpCode;

    /**
     * @var string
     */
    private string $responseContent;

    public function __construct(int $httpCode, string $responseContent)
    {
        $this->httpCode = $httpCode;
        $this->responseContent = $responseContent;

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getResponseContent(): string
    {
        return $this->responseContent;
    }
}
