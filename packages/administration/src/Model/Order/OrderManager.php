<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Order;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderFacade $orderFacade,
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
        return Order::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function createDataObject(): OrderData
    {
        return $this->orderDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $object
     */
    public function doDelete(object $object): void
    {
        $this->orderFacade->deleteById($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $dataObject
     * @return object
     */
    public function doCreate(AdminIdentifierInterface $dataObject): object
    {
        throw new Exception('Creation of order in administration is not supported.');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $dataObject
     * @return object
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->orderFacade->edit($dataObject->getId(), $dataObject);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->orderDataFactory->createFromOrder($entity);
    }
}
