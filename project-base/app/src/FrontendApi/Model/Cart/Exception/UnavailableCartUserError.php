<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart\Exception;

use Overblog\GraphQLBundle\Error\UserError;

use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class UnavailableCartUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'cart-unavailable';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
