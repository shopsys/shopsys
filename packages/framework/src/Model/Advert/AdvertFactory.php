<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdvertFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(AdvertData $data): Advert
    {
        $entityClassName = $this->entityNameResolver->resolve(Advert::class);

        return new $entityClassName($data);
    }
}
