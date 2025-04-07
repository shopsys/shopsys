<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

class EntityConfig
{
    private const string ENTITY_NAMESPACE_PATTERN = 'App\\Model\\%s\\';

    public string $entityName;

    public string $tableName;

    public bool $isTranslatable;

    public bool $isMultiDomain;

    public bool $hasId;

    public bool $hasUuid;

    /**
     * @var \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
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
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityTypeEnum $entityType
     * @return string
     */
    public function getEntityFullyQualifiedName(EntityTypeEnum $entityType = EntityTypeEnum::ENTITY): string
    {
        $entityName = $this->getEntityNamespace() . $this->entityName;

        if ($entityType === EntityTypeEnum::TRANSLATION) {
            $entityName .= 'Translation';
        } elseif ($entityType === EntityTypeEnum::DOMAIN) {
            $entityName .= 'Domain';
        }

        return $entityName;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\Property $entityProperty
     */
    public function addProperty(Property $entityProperty): void
    {
        $this->properties[] = $entityProperty;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
     */
    public function getAllProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
     */
    public function getTranslationPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::TRANSLATION);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
     */
    public function getEntityPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::ENTITY);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
     */
    public function getDomainPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::DOMAIN);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property|null
     */
    public function findFirstDomainProperty(): ?Property
    {
        $domainPropertiesOnly = $this->getDomainPropertiesOnly();

        if (count($domainPropertiesOnly) === 0) {
            return null;
        }

        return reset($domainPropertiesOnly);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityTypeEnum $entityTypeEnum
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\Property[]
     */
    private function filterPropertiesByEntityType(EntityTypeEnum $entityTypeEnum): array
    {
        return array_filter($this->properties, static fn (Property $property) => $property->entityType === $entityTypeEnum);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityTypeEnum $fieldTargetEnum
     * @return bool
     */
    public function hasAnyRelationOfCollectionType(EntityTypeEnum $fieldTargetEnum = EntityTypeEnum::ENTITY): bool
    {
        foreach ($this->filterPropertiesByEntityType($fieldTargetEnum) as $property) {
            if ($property->isCollection()) {
                return true;
            }
        }

        return false;
    }
}
