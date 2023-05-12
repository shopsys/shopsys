<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdvertFactory implements AdvertFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $data
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function create(AdvertData $data): Advert
    {
        $classData = $this->entityNameResolver->resolve(Advert::class);

        return new $classData($data);
    }
}
