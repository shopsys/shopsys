<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidPacketeryAddressIdUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
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
