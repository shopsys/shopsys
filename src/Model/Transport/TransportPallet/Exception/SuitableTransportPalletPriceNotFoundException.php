<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPallet\Exception;

use App\Model\Transport\Transport;
use Exception;
use Throwable;

class SuitableTransportPalletPriceNotFoundException extends Exception
{
    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @param \Throwable|null $previous
     */
    public function __construct(Transport $transport, int $domainId, ?Throwable $previous = null)
    {
        $message = sprintf(
            'Transport with ID=`%s` and domainId=`%s` does not have suitable price level.',
            $transport->getId(),
            $domainId
        );

        parent::__construct($message, 0, $previous);
    }
}
