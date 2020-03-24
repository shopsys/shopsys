<?php

declare(strict_types=1);

namespace App\Model\Order\Preview\Exception;

use App\Model\Product\Type\ProductType;
use App\Model\Transport\Transport;
use Exception;
use Throwable;

class TransportPriceNotFoundException extends Exception
{
    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @param \App\Model\Transport\Transport $transport
     * @param \Throwable|null $previous
     */
    public function __construct(ProductType $productType, Transport $transport, ?Throwable $previous = null)
    {
        $message = sprintf(
            'Price for Transport (ID=`%s`) and ProductType (ID=`%s`) was not found. Is the combination allowed?',
            $transport->getId(),
            $productType->getId()
        );
        parent::__construct($message, 0, $previous);
    }
}
