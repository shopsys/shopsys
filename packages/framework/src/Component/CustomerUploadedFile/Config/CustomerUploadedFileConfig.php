<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\CustomerUploadedFileEntityConfigNotFoundException;

class CustomerUploadedFileConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileEntityConfig[] $customerUploadedFileEntityConfigsByClass
     */
    public function __construct(protected readonly array $customerUploadedFileEntityConfigsByClass)
    {
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getEntityName(object $entity): string
    {
        return $this->getCustomerUploadedFileEntityConfig($entity)->getEntityName();
    }

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileEntityConfig
     */
    public function getCustomerUploadedFileEntityConfig(object $entity): CustomerUploadedFileEntityConfig
    {
        foreach ($this->customerUploadedFileEntityConfigsByClass as $className => $entityConfig) {
            if ($entity instanceof $className) {
                return $entityConfig;
            }
        }

        throw new CustomerUploadedFileEntityConfigNotFoundException(get_class($entity));
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function hasCustomerUploadedFileEntityConfig(object $entity): bool
    {
        foreach (array_keys($this->customerUploadedFileEntityConfigsByClass) as $className) {
            if ($entity instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileEntityConfig[]
     */
    public function getAllCustomerUploadedFileEntityConfigs(): array
    {
        return $this->customerUploadedFileEntityConfigsByClass;
    }
}
