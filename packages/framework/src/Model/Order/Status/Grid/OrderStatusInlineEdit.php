<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Order\Status\OrderStatusFormType;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\Form\FormFactoryInterface;

class OrderStatusInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\Grid\OrderStatusGridFactory $orderStatusGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface $orderStatusDataFactory
     */
    public function __construct(
        OrderStatusGridFactory $orderStatusGridFactory,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly OrderStatusDataFactoryInterface $orderStatusDataFactory,
    ) {
        parent::__construct($orderStatusGridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @return int
     */
    protected function createEntityAndGetId($orderStatusData)
    {
        $orderStatus = $this->orderStatusFacade->create($orderStatusData);

        return $orderStatus->getId();
    }

    /**
     * @param int $orderStatusId
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    protected function editEntity($orderStatusId, $orderStatusData)
    {
        $this->orderStatusFacade->edit($orderStatusId, $orderStatusData);
    }

    /**
     * @param int|null $orderStatusId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($orderStatusId)
    {
        if ($orderStatusId !== null) {
            $orderStatus = $this->orderStatusFacade->getById((int)$orderStatusId);
            $orderStatusData = $this->orderStatusDataFactory->createFromOrderStatus($orderStatus);
        } else {
            $orderStatusData = $this->orderStatusDataFactory->create();
        }

        return $this->formFactory->create(OrderStatusFormType::class, $orderStatusData);
    }
}
