<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;

class PlaceOrderFacadeTest extends TestCase
{
    public function testTwoItemsWithSamePromoCodeDecreasesOnlyOnce(): void
    {
        $promoCode = $this->createMock(PromoCode::class);
        $promoCode->method('getId')->willReturn(1);
        $promoCode->expects($this->once())->method('decreaseRemainingUses');

        $discountItem1 = new OrderItemData();
        $discountItem1->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem1->promoCode = $promoCode;

        $discountItem2 = new OrderItemData();
        $discountItem2->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem2->promoCode = $promoCode;

        $orderData = new OrderData();
        $orderData->items = [$discountItem1, $discountItem2];
        $orderData->freeTransportAndPaymentApplied = false;

        $placeOrderFacade = $this->createPlaceOrderFacadeMock();
        $placeOrderFacade->placeOrder($orderData);
    }

    public function testTwoItemsWithDifferentPromoCodesDecreasesBoth(): void
    {
        $promoCode1 = $this->createMock(PromoCode::class);
        $promoCode1->method('getId')->willReturn(1);
        $promoCode1->expects($this->once())->method('decreaseRemainingUses');

        $promoCode2 = $this->createMock(PromoCode::class);
        $promoCode2->method('getId')->willReturn(2);
        $promoCode2->expects($this->once())->method('decreaseRemainingUses');

        $discountItem1 = new OrderItemData();
        $discountItem1->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem1->promoCode = $promoCode1;

        $discountItem2 = new OrderItemData();
        $discountItem2->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem2->promoCode = $promoCode2;

        $orderData = new OrderData();
        $orderData->items = [$discountItem1, $discountItem2];
        $orderData->freeTransportAndPaymentApplied = false;

        $placeOrderFacade = $this->createPlaceOrderFacadeMock();
        $placeOrderFacade->placeOrder($orderData);
    }

    public function testThreeItemsWithTwoPromoCodesDecreasesBothOnce(): void
    {
        $promoCode1 = $this->createMock(PromoCode::class);
        $promoCode1->method('getId')->willReturn(1);
        $promoCode1->expects($this->once())->method('decreaseRemainingUses');

        $promoCode2 = $this->createMock(PromoCode::class);
        $promoCode2->method('getId')->willReturn(2);
        $promoCode2->expects($this->once())->method('decreaseRemainingUses');

        $discountItem1 = new OrderItemData();
        $discountItem1->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem1->promoCode = $promoCode1;

        $discountItem2 = new OrderItemData();
        $discountItem2->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem2->promoCode = $promoCode2;

        $discountItem3 = new OrderItemData();
        $discountItem3->type = OrderItemTypeEnum::TYPE_DISCOUNT;
        $discountItem3->promoCode = $promoCode1;

        $orderData = new OrderData();
        $orderData->items = [$discountItem1, $discountItem2, $discountItem3];
        $orderData->freeTransportAndPaymentApplied = false;

        $placeOrderFacade = $this->createPlaceOrderFacadeMock();
        $placeOrderFacade->placeOrder($orderData);
    }

    private function createPlaceOrderFacadeMock(): PlaceOrderFacade
    {
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getCustomerUser')->willReturn(null);

        $placeOrderFacade = $this->getMockBuilder(PlaceOrderFacade::class)
            ->setConstructorArgs([
                $this->createMock(OrderStatusRepository::class),
                $this->createMock(OrderNumberSequenceRepository::class),
                $this->createMock(OrderHashGeneratorRepository::class),
                $this->createMock(OrderFactory::class),
                $this->createMock(EntityManagerInterface::class),
                $this->createMock(OrderItemFactory::class),
                $this->createMock(PlacedOrderMessageDispatcher::class),
                $this->createMock(NewsletterFacade::class),
                $this->createMock(CustomerUserFacade::class),
                $this->createMock(PromoCodeFacade::class),
            ])
            ->onlyMethods(['createOrderOnly'])
            ->getMock();

        $placeOrderFacade->method('createOrderOnly')->willReturn($order);

        return $placeOrderFacade;
    }
}
