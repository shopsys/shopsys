<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
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
        foreach ($orderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT) as $discount) {
            $discount->promoCode->decreaseRemainingUses();
        }

        $order = $this->createOrderOnly($orderData);

        $customerUser = $order->getCustomerUser();

        if ($customerUser !== null) {
            $this->customerUserFacade->updateCustomerUserByOrder(
                $customerUser,
                $order,
                $deliveryAddressUuid,
                (bool)$orderData->newsletterSubscription,
            );
        } elseif ($orderData->newsletterSubscription) {
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
    public function createOrderOnly(OrderData $orderData): Order
    {
        if ($orderData->status === null) {
            $status = $this->orderStatusRepository->getDefault();
            $orderData->status = $status;
        }

        $orderNumber = $this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();

        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $orderData->customerUser,
        );

        $this->em->persist($order);

        $this->fillOrderItems($order, $orderData);

        $order->setTotalPrices($orderData->totalPrice, $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PRODUCT]);

        $this->em->flush();

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function fillOrderItems(Order $order, OrderData $orderData): void
    {
        $alreadyCreatedOrderItems = [];

        foreach ($orderData->items as $orderItemData) {
            if (array_key_exists($this->generateCacheKey($orderItemData), $alreadyCreatedOrderItems)) {
                continue;
            }

            $orderItem = $this->createSpecificOrderItem($orderItemData, $order);
            $alreadyCreatedOrderItems[$this->generateCacheKey($orderItemData)] = $orderItem;

            $this->em->persist($orderItem);

            foreach ($orderItemData->relatedOrderItemsData as $relatedOrderItemData) {
                if (array_key_exists($this->generateCacheKey($relatedOrderItemData), $alreadyCreatedOrderItems)) {
                    $relatedOrderItem = $alreadyCreatedOrderItems[$this->generateCacheKey($relatedOrderItemData)];
                    $orderItem->addRelatedItem($relatedOrderItem);

                    continue;
                }

                $relatedOrderItem = $this->createSpecificOrderItem($relatedOrderItemData, $order);
                $alreadyCreatedOrderItems[$this->generateCacheKey($relatedOrderItemData)] = $relatedOrderItem;

                $this->em->persist($relatedOrderItem);

                $orderItem->addRelatedItem($relatedOrderItem);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @return string
     */
    protected function generateCacheKey(OrderItemData $orderItemData): string
    {
        return spl_object_hash($orderItemData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    protected function createSpecificOrderItem(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        return match ($orderItemData->type) {
            OrderItemTypeEnum::TYPE_PRODUCT => $this->orderItemFactory->createProduct(
                $orderItemData,
                $order,
                $orderItemData->product,
            ),
            OrderItemTypeEnum::TYPE_TRANSPORT => $this->orderItemFactory->createTransport(
                $orderItemData,
                $order,
                $orderItemData->transport,
            ),
            OrderItemTypeEnum::TYPE_PAYMENT => $this->orderItemFactory->createPayment(
                $orderItemData,
                $order,
                $orderItemData->payment,
            ),
            OrderItemTypeEnum::TYPE_DISCOUNT => $this->orderItemFactory->createDiscount(
                $orderItemData,
                $order,
            ),
            OrderItemTypeEnum::TYPE_ROUNDING => $this->orderItemFactory->createRounding(
                $orderItemData,
                $order,
            ),
            default => $this->orderItemFactory->createOrderItem($orderItemData, $order),
        };
    }
}
