<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class PurchasedGiftVoucherAlreadyRedeemedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'purchased-gift-voucher-already-redeemed';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
