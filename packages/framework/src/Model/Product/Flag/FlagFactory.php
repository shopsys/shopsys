<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class FlagFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(FlagData $data): Flag
    {
        $entityClassName = $this->entityNameResolver->resolve(Flag::class);

        return new $entityClassName($data);
    }
}
