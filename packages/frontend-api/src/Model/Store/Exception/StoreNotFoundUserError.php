<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class StoreNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'store-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
