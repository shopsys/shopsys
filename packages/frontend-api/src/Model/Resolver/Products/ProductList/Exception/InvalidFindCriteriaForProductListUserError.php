<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidFindCriteriaForProductListUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-find-criteria-for-product-list';

    /**
     * @return string
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
