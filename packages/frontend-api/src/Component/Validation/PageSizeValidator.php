<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Validation;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Component\Validation\Exception\MaxAllowedLimitUserError;

class PageSizeValidator
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param int $maxAllowedItems
     */
    public static function checkMaxPageSize(
        Argument $argument,
        int $maxAllowedItems = 100,
    ): void {
        if ((isset($argument['first']) && $argument['first'] > $maxAllowedItems)
            || (isset($argument['last']) && $argument['last'] > $maxAllowedItems)
        ) {
            throw new MaxAllowedLimitUserError('Required amount of items exceeds maximum allowed limit.');
        }
    }
}
