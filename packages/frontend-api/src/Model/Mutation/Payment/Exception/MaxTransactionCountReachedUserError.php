<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class MaxTransactionCountReachedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'max-transaction-count-reached';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
