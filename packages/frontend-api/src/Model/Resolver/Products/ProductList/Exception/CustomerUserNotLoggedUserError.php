<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class CustomerUserNotLoggedUserError extends UserError implements UserErrorWithCodeInterface
{
    public const CODE = 'customer-user-not-logged';

    /**
     * @return string
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
