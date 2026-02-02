<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\PersonalData\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidPersonalDataRequestTypeUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'personal-data-request-type-invalid';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
