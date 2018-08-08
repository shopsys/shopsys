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
    
    public function getEntityName(Object $entity): string
    {
        return $this->getUploadedFileEntityConfig($entity)->getEntityName();
    }
    
    public function getUploadedFileEntityConfig(Object $entity): \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
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
    
    public function hasUploadedFileEntityConfig(object $entity): bool
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
