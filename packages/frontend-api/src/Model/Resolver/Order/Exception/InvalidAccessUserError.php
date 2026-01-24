<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidAccessUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-access';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
