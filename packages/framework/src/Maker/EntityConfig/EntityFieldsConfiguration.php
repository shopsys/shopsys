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
    public function getAllProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    public function getTranslationPropertiesOnly(): array
    {
        return $this->filterPropertiesByTarget(PropertyTargetEnum::TRANSLATION);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    public function getEntityPropertiesOnly(): array
    {
        return $this->filterPropertiesByTarget(PropertyTargetEnum::ENTITY);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\PropertyTargetEnum $propertyTargetEnum
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    private function filterPropertiesByTarget(PropertyTargetEnum $propertyTargetEnum): array
    {
        return array_filter($this->properties, static fn (EntityProperty $property) => $property->propertyTarget === $propertyTargetEnum);
    }
}
