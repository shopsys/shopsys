<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidCartItemUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'cart-item-invalid';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
