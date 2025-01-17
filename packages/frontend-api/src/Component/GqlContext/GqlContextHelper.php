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
    public function getArgs(?ArrayObject $context): ArrayObject
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
    public function getCartUuid(?ArrayObject $context): ?string
    {
        return $this->getArgs($context)['cartUuid'] ?? null;
    }

    /**
     * @param \ArrayObject|null $context
     * @return string|null
     */
    public function getOrderUuid(?ArrayObject $context): ?string
    {
        return $this->getArgs($context)['orderUuid'] ?? null;
    }
}
