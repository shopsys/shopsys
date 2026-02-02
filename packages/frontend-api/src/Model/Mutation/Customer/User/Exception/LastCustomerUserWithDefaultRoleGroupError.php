<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class LastCustomerUserWithDefaultRoleGroupError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'last-customer-user-with-default-role-group';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
