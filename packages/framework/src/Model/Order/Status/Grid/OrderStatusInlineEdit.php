<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Order\Status\OrderStatusFormType;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class OrderStatusInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade
     */
    protected $orderStatusFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface
     */
    protected $orderStatusDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\Grid\OrderStatusGridFactory $orderStatusGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactoryInterface $orderStatusDataFactory
     */
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

    /**
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $orderStatus = $this->orderStatusFacade->create($formData);

        return $orderStatus->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->orderStatusFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $orderStatusId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($orderStatusId): FormInterface
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
