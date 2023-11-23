<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Payment\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderAlreadyPaidUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'order-already-paid';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
