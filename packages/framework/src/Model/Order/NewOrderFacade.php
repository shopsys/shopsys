<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class NewOrderFacade
{
    public function createOrder(
        OrderData $orderData,
        CustomerUser $customerUser,
    ): Order {
        foreach ($orderData->appliedPromoCodes as $promoCode) {
            $promoCode->decreaseRemainingUses();
        }

        // todo mapping dto na data? nebude lepší mít přímo data v procesoru?
        // kde všude se data používají a jak?

        if ($orderData->status === null) {
            /** @var \App\Model\Order\Status\OrderStatus $status */
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

        $this->fillOrderItems($order, $orderPreview);

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order),
        );

        $this->em->flush();

        return $order;
    }
}
