<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'order-not-found';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
