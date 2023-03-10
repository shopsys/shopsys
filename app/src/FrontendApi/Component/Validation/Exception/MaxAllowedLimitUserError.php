<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\Validation\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class MaxAllowedLimitUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'max-allowed-limit';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
