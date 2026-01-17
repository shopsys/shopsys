<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

interface UploadedFileConfigInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    public function getAllUploadedFileEntityConfigs(): array;

    public function hasUploadedFileEntityConfig(object $entity): bool;

    public function getUploadedFileEntityConfig(object $entity): UploadedFileEntityConfig;

    public function getEntityName(object $entity): string;

    public function getUploadedFileEntityConfigByClass(string $entityClass): UploadedFileEntityConfig;

    public function isRequiredFriendlyName(
        string $entityName,
        string $typeName = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): bool;
}
