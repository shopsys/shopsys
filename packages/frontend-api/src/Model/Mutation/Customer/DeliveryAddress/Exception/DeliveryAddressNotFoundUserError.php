<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class DeliveryAddressNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'delivery-address-not-found';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
