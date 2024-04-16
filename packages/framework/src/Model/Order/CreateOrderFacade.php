<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;

class CreateOrderFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderNumberSequenceRepository $orderNumberSequenceRepository,
        protected readonly OrderHashGeneratorRepository $orderHashGeneratorRepository,
        protected readonly OrderFactory $orderFactory,
        protected readonly EntityManagerInterface $em,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly OrderItemFactory $orderItemFactory,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrder(
        OrderData $orderData,
        //@todo maybe customer user is not necessary as it may be part of orderdata - to avoid misuse
        ?CustomerUser $customerUser,
    ): Order {
        foreach ($orderData->getItemsByType(OrderItem::TYPE_DISCOUNT) as $discount) {
            $discount->promoCode->decreaseRemainingUses();
        }

        if ($orderData->status === null) {
            $status = $this->orderStatusRepository->getDefault();
            $orderData->status = $status;
        }

        $orderNumber = (string)$this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();

        $this->setOrderDataAdministrator($orderData);

        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $customerUser,
        );

        $this->em->persist($order);

        $this->fillOrderItems($order, $orderData);

        $order->setTotalPrices($orderData->totalPrice, $orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT]);

        $this->em->flush();

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setOrderDataAdministrator(OrderData $orderData): void
    {
        if ($this->administratorFrontSecurityFacade->isAdministratorLoggedAsCustomer()) {
            try {
                $currentAdmin = $this->administratorFrontSecurityFacade->getCurrentAdministrator();
                $orderData->createdAsAdministrator = $currentAdmin;
                $orderData->createdAsAdministratorName = $currentAdmin->getRealName();
            } catch (AdministratorIsNotLoggedException) {
                return;
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderItems(Order $order, OrderData $orderData): void
    {
        $this->fillOrderProducts($order, $orderData);
        $this->fillOrderPayment($order, $orderData);
        $this->fillOrderTransport($order, $orderData);
        $this->fillOrderDiscounts($order, $orderData);
        $this->fillOrderRounding($order, $orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderDiscounts(Order $order, OrderData $orderData): void
    {
        foreach ($orderData->getItemsByType(OrderItem::TYPE_DISCOUNT) as $orderItemData) {
            $orderItem = $this->orderItemFactory->createDiscount(
                $orderItemData,
                $order,
            );

            $this->em->persist($orderItem);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderProducts(Order $order, OrderData $orderData): void
    {
        foreach ($orderData->getItemsByType(OrderItem::TYPE_PRODUCT) as $orderItemData) {
            $orderItem = $this->orderItemFactory->createProduct(
                $orderItemData,
                $order,
                $orderItemData->product,
            );

            $this->em->persist($orderItem);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderPayment(Order $order, OrderData $orderData): void
    {
        $payment = $orderData->payment;

        $orderPaymentsData = $orderData->getItemsByType(OrderItem::TYPE_PAYMENT);

        foreach ($orderPaymentsData as $orderPaymentData) {
            $orderPayment = $this->orderItemFactory->createPayment(
                $orderPaymentData,
                $order,
                $payment,
            );

            $this->em->persist($orderPayment);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderTransport(Order $order, OrderData $orderData): void
    {
        $transport = $orderData->transport;

        $orderTransportsData = $orderData->getItemsByType(OrderItem::TYPE_TRANSPORT);

        foreach ($orderTransportsData as $orderTransportData) {
            $orderTransport = $this->orderItemFactory->createTransport(
                $orderTransportData,
                $order,
                $transport,
            );

            $this->em->persist($orderTransport);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderRounding(Order $order, OrderData $orderData): void
    {
        $orderRoundingsData = $orderData->getItemsByType(OrderItem::TYPE_ROUNDING);

        foreach ($orderRoundingsData as $orderRoundingData) {
            $orderRounding = $this->orderItemFactory->createRounding(
                $orderRoundingData,
                $order,
            );

            $this->em->persist($orderRounding);
        }
    }
}
