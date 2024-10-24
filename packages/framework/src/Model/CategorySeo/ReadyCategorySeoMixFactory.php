<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ReadyCategorySeoMixFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData $readyCategorySeoMixData
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function create(
        ReadyCategorySeoMixData $readyCategorySeoMixData,
    ): ReadyCategorySeoMix {
        $entityClassName = $this->entityNameResolver->resolve(ReadyCategorySeoMix::class);

        return new $entityClassName($readyCategorySeoMixData);
    }
}
