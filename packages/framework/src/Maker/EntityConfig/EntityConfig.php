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
}
