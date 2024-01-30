<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SliderItemFactory implements SliderItemFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $data
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function create(SliderItemData $data): SliderItem
    {
        $entityClassName = $this->entityNameResolver->resolve(SliderItem::class);

        return new $entityClassName($data);
    }
}
