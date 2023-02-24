<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\Exception;

use Overblog\GraphQLBundle\Error\UserError;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class DeliveryAddressNotFoundUserError extends UserError //TODO-RK nahrad UserError za: UserEntityNotFoundError implements UserErrorWithCodeInterface
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
