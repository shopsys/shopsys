<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class WithdrawalConfirmationInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'order-withdrawal-confirmation-invalid';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
