<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Payment\Exception;

use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class PaymentNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    protected const CODE = 'payment-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
