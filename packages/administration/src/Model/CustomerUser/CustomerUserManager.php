<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\CustomerUser;

use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CustomerUserManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
        EntityNameResolver $entityNameResolver,
        ManagerRegistry $registry,
        PropertyAccessorInterface $propertyAccessor,
    ) {
        parent::__construct($entityNameResolver, $registry, $propertyAccessor);
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return CustomerUser::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createDataObject(): CustomerUserData
    {
        return $this->customerUserDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function doCreate(AdminIdentifierInterface $dataObject): CustomerUser
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $object
     */
    public function doDelete(object $object): void
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        throw new RuntimeException('Not implemented');
    }
}
