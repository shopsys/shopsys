<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use RuntimeException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Throwable;

class OrderItemHasOnlyOneTotalPriceException extends RuntimeException
{
    public function __construct(?Money $totalPriceWithVat, ?Money $totalPriceWithoutVat, ?Throwable $previous = null)
    {
        $message = sprintf(
            'Order item has only one of its total prices set: %s with VAT / %s without VAT',
            $totalPriceWithVat !== null ? $totalPriceWithVat->getAmount() : 'NULL',
            $totalPriceWithoutVat !== null ? $totalPriceWithoutVat->getAmount() : 'NULL',
        );

        parent::__construct($message, 0, $previous);
    }
}
