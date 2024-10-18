<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order\Exception;

use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Throwable;

class OrderDetailNotFoundException extends ConvertimException
{
    /**
     * @param string $email
     * @param \Throwable|null $previous
     */
    public function __construct(string $email, Throwable $previous = null)
    {
        parent::__construct('Order detail not found', ['email' => $email], 404, $previous);
    }
}
