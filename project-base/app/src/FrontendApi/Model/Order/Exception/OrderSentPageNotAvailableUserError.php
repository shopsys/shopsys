<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderException;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderSentPageNotAvailableUserError extends UserError implements OrderException, UserErrorWithCodeInterface
{
    private const CODE = 'order-sent-page-not-available';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
