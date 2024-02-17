<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin\Order;

use Shopsys\AdminBundle\Component\Admin\AbstractDtoManager;
use Shopsys\AdminBundle\Component\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Contracts\Service\Attribute\Required;

class OrderManager extends AbstractDtoManager
{
    #[Required]
    public OrderDataFactory $orderDataFactory;

    #[Required]
    public OrderFacade $orderFacade;

    public function getSubjectClass()
    {
        return Order::class;
    }

    public function createDataObject()
    {
        return $this->orderDataFactory->create();
    }

    public function doDelete(object $object)
    {
        $this->orderFacade->deleteById($object->getId());
    }

    public function doCreate(AdminIdentifierInterface $dataObject): object
    {
        return $this->orderFacade->createOrder($dataObject);
    }

    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
       return $this->orderFacade->edit($dataObject->getId(), $dataObject);
    }

    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
       return $this->orderDataFactory->createFromOrder($entity);
    }


}