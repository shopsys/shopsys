<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Order;

use Exception;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;

class OrderManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderFacade $orderFacade,
    ) {
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
