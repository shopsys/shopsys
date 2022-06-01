<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderException;

class OrderNotFoundException extends UserError implements OrderException, UserErrorWithCodeInterface
{
    private const CODE = 'order-not-found';

    /**
     * @param string $message
     */
    public function __construct(string $message = 'Order not found.')
    {
        parent::__construct($message, 404);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
