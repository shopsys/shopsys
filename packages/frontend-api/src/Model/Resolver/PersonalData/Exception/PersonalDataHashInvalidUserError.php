<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\PersonalData\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class PersonalDataHashInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'personal-data-hash-invalid';

    #[Override]
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
