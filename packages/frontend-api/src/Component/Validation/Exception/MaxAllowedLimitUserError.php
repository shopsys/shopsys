<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Validation\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class MaxAllowedLimitUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'max-allowed-limit';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
