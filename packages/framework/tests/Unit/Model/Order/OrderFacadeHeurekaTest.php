<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderDeliveryDateFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class OrderFacadeHeurekaTest extends TestCase
{
    public function testNotSendHeurekaOrderInfoWhenShopCertificationIsNotActivated(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->expects($this->any())->method('isHeurekaShopCertificationActivated')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    public function testNotSendHeurekaOrderInfoWhenDomainLocaleNotSupported(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->expects($this->any())->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->expects($this->any())->method('isDomainLocaleSupported')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    public function testNotSendHeurekaOrderInfoForOrderWithoutAgreement(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->expects($this->any())->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->expects($this->any())->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, false);
    }

    public function testSendHeurekaOrderInfo(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->expects($this->any())->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->expects($this->any())->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->once())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    private function createOrderFacade(HeurekaFacade $heurekaFacade, Order $order): OrderFacade
    {
        $orderRepositoryStub = $this->createStub(OrderRepository::class);
        $orderRepositoryStub->method('getById')->willReturn($order);

        return new OrderFacade(
            $this->createStub(EntityManagerInterface::class),
            $orderRepositoryStub,
            $this->createStub(OrderStatusFacade::class),
            $this->createStub(OrderMailFacade::class),
            $this->createStub(Localization::class),
            $heurekaFacade,
            $this->createDomain(),
            $this->createStub(OrderPriceCalculation::class),
            $this->createStub(OrderItemPriceCalculation::class),
            $this->createStub(PaymentPriceCalculation::class),
            $this->createStub(OrderItemFactory::class),
            $this->createStub(OrderItemDataFactory::class),
            $this->createStub(OrderDataFactory::class),
            $this->createStub(PricingSetting::class),
            $this->createStub(OrderInputFactory::class),
            $this->createStub(OrderProcessor::class),
            $this->createStub(PaymentFacade::class),
            $this->createStub(OrderDeliveryDateFacade::class),
            $this->createStub(WithdrawalRequestFacade::class),
        );
    }

    private function runHeurekaTest(HeurekaFacade $heurekaFacade, bool $heurekaAgreement = true): void
    {
        $order = $this->createOrderStub($heurekaAgreement);
        $orderFacade = $this->createOrderFacade($heurekaFacade, $order);
        $orderFacade->sendHeurekaOrderInfo($order->getId());
    }

    private function createDomain(): Domain
    {
        $domainConfig = DomainConfigHelper::getDomainConfig();
        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn($domainConfig);

        return $domain;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\Stub|\Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function createOrderStub(bool $heurekaAgreement): Order
    {
        $order = $this->createStub(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);
        $order->method('isHeurekaAgreement')->willReturn($heurekaAgreement);

        return $order;
    }
}
