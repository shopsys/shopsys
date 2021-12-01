<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Throwable;

class UnavailableCartUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'cart-unavailable';

    /**
     * @param string $message
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
