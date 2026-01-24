<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidPacketeryAddressIdUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'packetery-address-id-invalid';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
