<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\Validation;

use App\FrontendApi\Component\Validation\Exception\MaxAllowedLimitUserError;
use Overblog\GraphQLBundle\Definition\Argument;

class PageSizeValidator
{
    private const DEFAULT_MAX_ALLOWED_ITEMS = 100;

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param int $maxAllowedItems
     */
    public static function checkMaxPageSize(Argument $argument, int $maxAllowedItems = self::DEFAULT_MAX_ALLOWED_ITEMS): void
    {
        if (isset($argument['first']) && $argument['first'] > $maxAllowedItems
            || isset($argument['last']) && $argument['last'] > $maxAllowedItems
        ) {
            throw new MaxAllowedLimitUserError('Required amount of items exceeds maximum allowed limit.');
        }
    }
}
