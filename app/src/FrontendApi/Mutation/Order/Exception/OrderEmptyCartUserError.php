<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class OrderEmptyCartUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'order-empty-cart';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
