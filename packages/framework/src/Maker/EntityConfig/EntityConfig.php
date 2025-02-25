<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

class EntityConfig
{
    private const string ENTITY_NAMESPACE_PATTERN = 'App\\Model\\%s\\';

    public string $entityName;

    public string $tableName;

    public bool $isTranslatable;

    public bool $hasId;

    public bool $hasUuid;

    /**
     * @var \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty[]
     */
    private array $properties = [];

    /**
     * @return string
     */
    public function getEntityNamespace(): string
    {
        return sprintf(self::ENTITY_NAMESPACE_PATTERN, ucfirst($this->entityName));
    }

    /**
     * @return string
     */
    public function getEntityFullyQualifiedName(): string
    {
        return $this->getEntityNamespace() . $this->entityName;
    }

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
