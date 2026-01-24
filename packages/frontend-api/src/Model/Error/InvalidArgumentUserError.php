<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use GraphQL\Error\UserError;
use Override;

class InvalidArgumentUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-argument';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
