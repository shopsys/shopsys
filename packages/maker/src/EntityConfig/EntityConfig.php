<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

class EntityConfig
{
    protected const string ENTITY_NAMESPACE_PATTERN = 'App\\Model\\%s\\';

    public string $entityName;

    public string $tableName;

    public bool $isTranslatable;

    public bool $isMultiDomain;

    public bool $hasId;

    public bool $hasUuid;

    /**
     * @var \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    protected array $properties = [];

    public function getEntityNamespace(): string
    {
        return sprintf(self::ENTITY_NAMESPACE_PATTERN, ucfirst($this->entityName));
    }

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

    public function addProperty(Property $entityProperty): void
    {
        $this->properties[] = $entityProperty;
    }

    /**
     * @return \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    public function getAllProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    public function getTranslationPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::TRANSLATION);
    }

    /**
     * @return \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    public function getEntityPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::ENTITY);
    }

    /**
     * @return \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    public function getDomainPropertiesOnly(): array
    {
        return $this->filterPropertiesByEntityType(EntityTypeEnum::DOMAIN);
    }

    public function findFirstDomainProperty(): ?Property
    {
        $domainPropertiesOnly = $this->getDomainPropertiesOnly();

        if (count($domainPropertiesOnly) === 0) {
            return null;
        }

        return array_first($domainPropertiesOnly);
    }

    /**
     * @return \Shopsys\MakerBundle\EntityConfig\Property[]
     */
    protected function filterPropertiesByEntityType(EntityTypeEnum $entityTypeEnum): array
    {
        return array_filter($this->properties, static fn (Property $property) => $property->entityType === $entityTypeEnum);
    }

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
