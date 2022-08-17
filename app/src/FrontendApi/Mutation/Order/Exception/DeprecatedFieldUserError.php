<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class DeprecatedFieldUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'deprecated-field';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
