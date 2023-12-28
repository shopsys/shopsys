<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Service\Exception;

use Exception;

class PaymentServiceFacadeNotRegisteredException extends Exception
{
    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        parent::__construct(sprintf('Payment service facade with type %s not registered.', $type));
    }
}
