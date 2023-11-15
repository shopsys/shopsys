<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class FeedModuleFactory implements FeedModuleFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedModule
     */
    public function create(string $name, int $domainId): FeedModule
    {
        $className = $this->entityNameResolver->resolve(FeedModule::class);

        return new $className($name, $domainId);
    }
}
