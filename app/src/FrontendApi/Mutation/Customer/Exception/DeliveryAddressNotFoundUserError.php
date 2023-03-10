<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class DeliveryAddressNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'delivery-address-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
