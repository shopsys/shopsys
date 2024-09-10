<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

interface UploadedFileConfigInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    public function getAllUploadedFileEntityConfigs(): array;

    /**
     * @param object $entity
     * @return bool
     */
    public function hasUploadedFileEntityConfig(object $entity): bool;

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
     */
    public function getUploadedFileEntityConfig(object $entity): UploadedFileEntityConfig;

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityName(object $entity): string;

    /**
     * @param string $entityClass
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
     */
    public function getUploadedFileEntityConfigByClass(string $entityClass): UploadedFileEntityConfig;
}
