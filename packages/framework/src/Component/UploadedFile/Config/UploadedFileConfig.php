<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

class UploadedFileConfig
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    private $uploadedFileEntityConfigsByClass;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[] $uploadedFileEntityConfigsByClass
     */
    public function __construct(array $uploadedFileEntityConfigsByClass)
    {
        $this->uploadedFileEntityConfigsByClass = $uploadedFileEntityConfigsByClass;
    }

    /**
     * @param Object $entity
     */
    public function getEntityName($entity): string
    {
        return $this->getUploadedFileEntityConfig($entity)->getEntityName();
    }

    /**
     * @param Object $entity
     */
    public function getUploadedFileEntityConfig($entity): \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
    {
        foreach ($this->uploadedFileEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileEntityConfigNotFoundException(
            $entity ? get_class($entity) : null
        );
    }

    /**
     * @param object $entity
     */
    public function hasUploadedFileEntityConfig($entity): bool
    {
        foreach ($this->uploadedFileEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    public function getAllUploadedFileEntityConfigs(): array
    {
        return $this->uploadedFileEntityConfigsByClass;
    }
}
