<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Override;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileEntityConfigNotFoundException;

class UploadedFileConfig implements UploadedFileConfigInterface
{
    /**
     * @param array<class-string, \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig> $uploadedFileEntityConfigsByClass
     */
    public function __construct(protected readonly array $uploadedFileEntityConfigsByClass)
    {
    }

    #[Override]
    public function getEntityName(object $entity): string
    {
        return $this->getUploadedFileEntityConfig($entity)->getEntityName();
    }

    #[Override]
    public function getUploadedFileEntityConfig(object $entity): UploadedFileEntityConfig
    {
        foreach ($this->uploadedFileEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new UploadedFileEntityConfigNotFoundException(get_class($entity));
    }

    #[Override]
    public function hasUploadedFileEntityConfig(object $entity): bool
    {
        foreach (array_keys($this->uploadedFileEntityConfigsByClass) as $className) {
            if ($entity instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    #[Override]
    public function getAllUploadedFileEntityConfigs(): array
    {
        return $this->uploadedFileEntityConfigsByClass;
    }

    #[Override]
    public function getUploadedFileEntityConfigByClass(string $entityClass): UploadedFileEntityConfig
    {
        foreach ($this->uploadedFileEntityConfigsByClass as $className => $entityConfig) {
            if (is_a($entityClass, $className, true)) {
                return $entityConfig;
            }
        }

        throw new UploadedFileEntityConfigNotFoundException($entityClass);
    }

    protected function getUploadedFileEntityConfigByName(string $entityName): UploadedFileEntityConfig
    {
        foreach ($this->uploadedFileEntityConfigsByClass as $entityConfig) {
            if ($entityConfig->getEntityName() === $entityName) {
                return $entityConfig;
            }
        }

        throw new UploadedFileEntityConfigNotFoundException($entityName);
    }

    #[Override]
    public function isRequiredFriendlyName(
        string $entityName,
        string $typeName = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): bool {
        return $this->getUploadedFileEntityConfigByName($entityName)
            ->getTypeByName($typeName)
            ->isRequiredFriendlyName();
    }
}
