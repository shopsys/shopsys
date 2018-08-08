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
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    private $orderStatusFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface
     */
    private $orderStatusDataFactory;

    public function __construct(
        OrderStatusGridFactory $orderStatusGridFactory,
        OrderStatusFacade $orderStatusFacade,
        FormFactoryInterface $formFactory,
        OrderStatusDataFactoryInterface $orderStatusDataFactory
    ) {
        parent::__construct($orderStatusGridFactory);
        $this->orderStatusFacade = $orderStatusFacade;
        $this->formFactory = $formFactory;
        $this->orderStatusDataFactory = $orderStatusDataFactory;
    }
    
    protected function createEntityAndGetId(\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData): int
    {
        $orderStatus = $this->orderStatusFacade->create($orderStatusData);

        return $orderStatus->getId();
    }
    
    protected function editEntity(int $orderStatusId, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData): void
    {
        $this->orderStatusFacade->edit($orderStatusId, $orderStatusData);
    }

    /**
     * @param int|null $orderStatusId
     */
    public function getForm(?int $orderStatusId): \Symfony\Component\Form\FormInterface
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
