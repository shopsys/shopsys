<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Administrator;

use Doctrine\Persistence\ManagerRegistry;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class AdministratorManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly AdministratorDataFactoryInterface $administratorDataFactory,
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
        return Administrator::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData
     */
    public function createDataObject(): AdministratorData
    {
        return $this->administratorDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function doCreate(AdminIdentifierInterface $dataObject): Administrator
    {
        return $this->administratorFacade->create($dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $object
     */
    public function doDelete(object $object): void
    {
        $this->administratorFacade->delete($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->administratorFacade->edit($dataObject->getId(), $dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->administratorDataFactory->createFromAdministrator($entity);
    }
}
