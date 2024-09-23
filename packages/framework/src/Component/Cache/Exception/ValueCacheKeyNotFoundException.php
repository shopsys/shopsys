<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cache\Exception;

class ValueCacheKeyNotFoundException extends InMemoryCacheException
{
    /**
     * @param string $namespace
     * @param string $valueKey
     */
    public function __construct(string $namespace, string $valueKey)
    {
        parent::__construct(sprintf('Value with keys "%s" and "%s" not found', $namespace, $valueKey));
    }
}
