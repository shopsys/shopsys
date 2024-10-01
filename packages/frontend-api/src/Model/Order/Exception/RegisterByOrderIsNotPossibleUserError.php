<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderException;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class RegisterByOrderIsNotPossibleUserError extends UserError implements OrderException, UserErrorWithCodeInterface
{
    protected const string CODE = 'register-by-order-is-not-possible';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
