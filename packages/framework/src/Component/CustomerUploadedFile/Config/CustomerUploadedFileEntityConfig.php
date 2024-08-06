<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\CustomerUploadedFileTypeConfigNotFoundException;

class CustomerUploadedFileEntityConfig
{
    /**
     * @param string $entityName
     * @param string $entityClass
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig[] $types
     */
    public function __construct(
        protected readonly string $entityName,
        protected readonly string $entityClass,
        protected readonly array $types,
    ) {
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $typeName
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig
     */
    public function getTypeByName(
        string $typeName = CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): CustomerUploadedFileTypeConfig {
        if (!array_key_exists($typeName, $this->types)) {
            throw new CustomerUploadedFileTypeConfigNotFoundException($typeName);
        }

        return $this->types[$typeName];
    }
}
