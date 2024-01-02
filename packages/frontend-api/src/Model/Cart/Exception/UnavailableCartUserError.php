<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class UnavailableCartUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'cart-unavailable';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
