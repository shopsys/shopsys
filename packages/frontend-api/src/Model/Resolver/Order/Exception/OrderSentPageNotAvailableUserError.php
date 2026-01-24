<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderSentPageNotAvailableUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'order-sent-page-not-available';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
