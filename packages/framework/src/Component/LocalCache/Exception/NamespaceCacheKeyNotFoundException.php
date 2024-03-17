<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\LocalCache\Exception;

class NamespaceCacheKeyNotFoundException extends LocalCacheException
{
    /**
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        parent::__construct(sprintf('Namespace cache key "%s" not found', $namespace));
    }
}
