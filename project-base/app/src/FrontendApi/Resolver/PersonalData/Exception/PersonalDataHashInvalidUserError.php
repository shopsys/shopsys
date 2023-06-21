<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\PersonalData\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class PersonalDataHashInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'personal-data-hash-invalid';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
