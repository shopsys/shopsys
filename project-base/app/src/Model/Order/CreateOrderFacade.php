<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Security\LoginAsUserFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Order\CreateOrderFacade as BaseCreateOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;

/**
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method fillOrderItems(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderDiscounts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderProducts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderTransport(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderRounding(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository, \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository, \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory, \Doctrine\ORM\EntityManagerInterface $em, \App\Model\Order\Item\OrderItemFactory $orderItemFactory)
 */
class CreateOrderFacade extends BaseCreateOrderFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \App\Model\Security\LoginAsUserFacade $loginAsUserFacade
     */
    public function __construct(
        OrderStatusRepository $orderStatusRepository,
        OrderNumberSequenceRepository $orderNumberSequenceRepository,
        OrderHashGeneratorRepository $orderHashGeneratorRepository,
        OrderFactory $orderFactory,
        EntityManagerInterface $em,
        OrderPriceCalculation $orderPriceCalculation,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        OrderItemFactory $orderItemFactory,
        OrderItemDataFactory $orderItemDataFactory,
        private readonly LoginAsUserFacade $loginAsUserFacade,
    ) {
        parent::__construct(
            $orderStatusRepository,
            $orderNumberSequenceRepository,
            $orderHashGeneratorRepository,
            $orderFactory,
            $em,
            $orderPriceCalculation,
            $administratorFrontSecurityFacade,
            $orderItemFactory,
            $orderItemDataFactory,
        );
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     */
    protected function setOrderDataAdministrator(OrderData $orderData): void
    {
        $currentAdministratorLoggedAsCustomer = $this->loginAsUserFacade->getCurrentAdministratorLoggedAsCustomer();

        if ($currentAdministratorLoggedAsCustomer === null) {
            return;
        }

        $orderData->createdAsAdministrator = $currentAdministratorLoggedAsCustomer;
        $orderData->createdAsAdministratorName = $currentAdministratorLoggedAsCustomer->getRealName();
    }
}
