<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderItemNotFoundUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'order-item-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
