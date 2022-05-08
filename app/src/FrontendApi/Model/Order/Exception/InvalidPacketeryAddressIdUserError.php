<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class InvalidPacketeryAddressIdUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'packetery-address-id-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
