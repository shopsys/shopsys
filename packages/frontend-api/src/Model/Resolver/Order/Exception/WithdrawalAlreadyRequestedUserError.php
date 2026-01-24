<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class WithdrawalAlreadyRequestedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'order-withdrawal-already-requested';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
