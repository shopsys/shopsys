<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\PersonalData\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidPersonalDataRequestTypeUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'personal-data-request-type-invalid';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
