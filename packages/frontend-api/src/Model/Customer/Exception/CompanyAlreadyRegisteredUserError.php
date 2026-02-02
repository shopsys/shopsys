<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class CompanyAlreadyRegisteredUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'company-already-registered';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
