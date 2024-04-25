<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;

class PlaceOrderFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderNumberSequenceRepository $orderNumberSequenceRepository,
        protected readonly OrderHashGeneratorRepository $orderHashGeneratorRepository,
        protected readonly OrderFactory $orderFactory,
        protected readonly EntityManagerInterface $em,
        protected readonly OrderItemFactory $orderItemFactory,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly PlacedOrderMessageDispatcher $placedOrderMessageDispatcher,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param string|null $deliveryAddressUuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function placeOrder(
        OrderData $orderData,
        ?string $deliveryAddressUuid = null,
    ): Order {
        foreach ($orderData->getItemsByType(OrderItem::TYPE_DISCOUNT) as $discount) {
            $discount->promoCode->decreaseRemainingUses();
        }

        $order = $this->createOrder($orderData);

        $customerUser = $order->getCustomerUser();

        if ($customerUser !== null) {
            $this->customerUserFacade->updateCustomerUserByOrder(
                $customerUser,
                $order,
                $deliveryAddressUuid,
                (bool)$orderData->newsletterSubscription,
            );
        } else {
            $this->newsletterFacade->addSubscribedEmailIfNotExists($order->getEmail(), $order->getDomainId());
        }

        $this->orderMailFacade->sendEmail($order);
        $this->placedOrderMessageDispatcher->dispatchPlacedOrderMessage($order->getId());

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrder(OrderData $orderData): Order
    {
        if ($orderData->status === null) {
            $status = $this->orderStatusRepository->getDefault();
            $orderData->status = $status;
        }

        $orderNumber = (string)$this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();

        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $orderData->customerUser,
        );

        $this->em->persist($order);

        $this->fillOrderItems($order, $orderData);

        $order->setTotalPrices($orderData->totalPrice, $orderData->totalPricesByItemType[OrderItem::TYPE_PRODUCT]);

        $this->em->flush();

        return $order;
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
