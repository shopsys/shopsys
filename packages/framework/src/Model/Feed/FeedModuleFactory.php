<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class FeedModuleFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(string $name, int $domainId): FeedModule
    {
        $className = $this->entityNameResolver->resolve(FeedModule::class);

        return new $className($name, $domainId);
    }
}
