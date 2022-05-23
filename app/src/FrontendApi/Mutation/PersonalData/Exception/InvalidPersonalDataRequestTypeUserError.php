<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\PersonalData\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class InvalidPersonalDataRequestTypeUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'personal-data-request-type-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
