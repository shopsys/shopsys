<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\PersonalData\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class PersonalDataHashInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'personal-data-hash-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
