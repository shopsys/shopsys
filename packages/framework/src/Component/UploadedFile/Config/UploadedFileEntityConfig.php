<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileTypeConfigNotFoundException;

class UploadedFileEntityConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig[] $types
     */
    public function __construct(
        protected readonly string $entityName,
        protected readonly string $entityClass,
        protected readonly array $types,
    ) {
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getTypeByName(string $typeName = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): UploadedFileTypeConfig
    {
        if (!array_key_exists($typeName, $this->types)) {
            throw new UploadedFileTypeConfigNotFoundException($typeName);
        }

        return $this->types[$typeName];
    }
}
