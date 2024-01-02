<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\GqlContext;

use ArrayObject;

class GqlContextHelper
{
    /**
     * @param \ArrayObject|null $context
     * @return \ArrayObject
     */
    public static function getArgs(?ArrayObject $context): ArrayObject
    {
        if ($context === null) {
            return new ArrayObject();
        }

        return isset($context['args']) ? new ArrayObject($context['args']) : new ArrayObject();
    }

    /**
     * @param \ArrayObject|null $context
     * @return string|null
     */
    public static function getCartUuid(?ArrayObject $context): ?string
    {
        return self::getArgs($context)['cartUuid'] ?? null;
    }

    /**
     * @param \ArrayObject|null $context
     * @return string|null
     */
    public static function getOrderUuid(?ArrayObject $context): ?string
    {
        return self::getArgs($context)['orderUuid'] ?? null;
    }
}
