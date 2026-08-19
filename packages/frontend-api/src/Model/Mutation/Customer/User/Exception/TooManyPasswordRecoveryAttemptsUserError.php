<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TooManyPasswordRecoveryAttemptsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'too-many-password-recovery-attempts';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
