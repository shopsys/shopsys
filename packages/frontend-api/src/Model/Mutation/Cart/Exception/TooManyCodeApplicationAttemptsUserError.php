<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TooManyCodeApplicationAttemptsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'too-many-code-application-attempts';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
