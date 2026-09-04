<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Detection\DetectionFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogSourceEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;

class GiftVoucherGenerationFacade
{
    public const string VALIDITY_MODIFIER = '+365 days';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GiftVoucherFactory $giftVoucherFactory,
        protected readonly GiftVoucherCodeGenerator $giftVoucherCodeGenerator,
        protected readonly GiftVoucherDataFactory $giftVoucherDataFactory,
        protected readonly GiftVoucherRepository $giftVoucherRepository,
        protected readonly DetectionFacade $detectionFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function generateForOrder(Order $order): array
    {
        $existingGiftVouchers = $this->giftVoucherRepository->getAllCreatedOnOrder($order);

        if ($existingGiftVouchers !== []) {
            return $existingGiftVouchers;
        }

        $this->detectionFacade->setSourceAndUserIdentifier(
            EntityLogSourceEnum::SYSTEM,
            $this->getCustomerIdentifier($order),
        );

        try {
            $giftVouchers = [];

            foreach ($this->getElectronicGiftVoucherOrderItems($order) as $orderItem) {
                for ($unit = 0; $unit < $orderItem->getQuantity(); $unit++) {
                    $giftVouchers[] = $this->createGiftVoucherForOrderItem($order, $orderItem);
                }
            }

            $this->em->flush();
        } finally {
            $this->detectionFacade->resetSourceAndUserIdentifier();
        }

        return $giftVouchers;
    }

    protected function getCustomerIdentifier(Order $order): string
    {
        $customerFullName = trim(sprintf('%s %s', $order->getFirstName(), $order->getLastName()));

        if ($customerFullName === '') {
            return $order->getEmail();
        }

        return sprintf('%s (%s)', $customerFullName, $order->getEmail());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    protected function getElectronicGiftVoucherOrderItems(Order $order): array
    {
        $orderItems = [];

        foreach ($order->getProductItems() as $orderItem) {
            $product = $orderItem->getProduct();

            if ($product !== null && $product->isElectronicGiftVoucher()) {
                $orderItems[] = $orderItem;
            }
        }

        return $orderItems;
    }

    protected function createGiftVoucherForOrderItem(Order $order, OrderItem $orderItem): GiftVoucher
    {
        $activatedAt = new DateTimeImmutable();

        $giftVoucherData = $this->giftVoucherDataFactory->create();
        $giftVoucherData->code = $this->giftVoucherCodeGenerator->generateUniqueCode();
        $giftVoucherData->domainId = $order->getDomainId();
        $giftVoucherData->valueWithVat = $this->getGiftVoucherValueWithVat($order, $orderItem);
        $giftVoucherData->currencyCode = $order->getCurrencyCode();
        $giftVoucherData->vatPercent = $orderItem->getVatPercent();
        $giftVoucherData->activatedAt = $activatedAt;
        $giftVoucherData->validUntil = $activatedAt->modify(static::VALIDITY_MODIFIER);
        $giftVoucherData->productCatnum = $orderItem->getCatnum();
        $giftVoucherData->productName = $orderItem->getName();
        $giftVoucherData->customerEmail = $order->getEmail();
        $giftVoucherData->createdOnOrder = $order;

        $giftVoucher = $this->giftVoucherFactory->create($giftVoucherData);
        $this->em->persist($giftVoucher);

        return $giftVoucher;
    }

    protected function getGiftVoucherValueWithVat(Order $order, OrderItem $orderItem): Money
    {
        return $orderItem->getUnitPriceWithVat();
    }
}
