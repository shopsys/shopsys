<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SliderItemFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(SliderItemData $data): SliderItem
    {
        $entityClassName = $this->entityNameResolver->resolve(SliderItem::class);

        return new $entityClassName($data);
    }
}
