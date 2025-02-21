<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

class EntityFieldsConfiguration
{
    /**
     * @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    private array $properties = [];

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty $entityProperty
     */
    public function addProperty(EntityProperty $entityProperty): void
    {
        $this->properties[] = $entityProperty;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
