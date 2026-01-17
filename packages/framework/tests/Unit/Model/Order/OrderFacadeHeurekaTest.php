<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
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
use Shopsys\FrameworkBundle\Model\Order\OrderFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class OrderFacadeHeurekaTest extends TestCase
{
    public function testNotSendHeurekaOrderInfoWhenShopCertificationIsNotActivated(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    public function testNotSendHeurekaOrderInfoWhenDomainLocaleNotSupported(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    public function testNotSendHeurekaOrderInfoForOrderWithoutAgreement(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, false);
    }

    public function testSendHeurekaOrderInfo(): void
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->once())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade);
    }

    private function createOrderFacade(HeurekaFacade $heurekaFacade, Order $order): OrderFacade
    {
        $orderRepositoryMock = $this->createMock(OrderRepository::class);
        $orderRepositoryMock->method('getById')->willReturn($order);

        return new OrderFacade(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(OrderNumberSequenceRepository::class),
            $orderRepositoryMock,
            $this->createMock(OrderUrlGenerator::class),
            $this->createMock(OrderStatusFacade::class),
            $this->createMock(OrderMailFacade::class),
            $this->createMock(OrderHashGeneratorRepository::class),
            $this->createMock(Setting::class),
            $this->createMock(Localization::class),
            $this->createMock(AdministratorFrontSecurityFacade::class),
            $this->createMock(CurrentPromoCodeFacade::class),
            $this->createMock(CartFacade::class),
            $this->createMock(CustomerUserFacade::class),
            $this->createMock(CurrentCustomerUser::class),
            $heurekaFacade,
            $this->createDomain(),
            $this->createMock(OrderFactory::class),
            $this->createMock(OrderPriceCalculation::class),
            $this->createMock(OrderItemPriceCalculation::class),
            $this->createMock(NumberFormatterExtension::class),
            $this->createMock(PaymentPriceCalculation::class),
            $this->createMock(TransportPriceCalculation::class),
            $this->createMock(OrderItemFactory::class),
            $this->createMock(PaymentTransactionFacade::class),
            $this->createMock(PaymentTransactionDataFactory::class),
            $this->createMock(PaymentServiceFacade::class),
            $this->createMock(OrderItemDataFactory::class),
            $this->createMock(OrderDataFactory::class),
            $this->createMock(PricingSetting::class),
            $this->createMock(OrderInputFactory::class),
            $this->createMock(OrderProcessor::class),
            $this->createMock(PaymentFacade::class),
            $this->createMock(OrderDeliveryDateFacade::class),
            $this->createMock(WithdrawalRequestFacade::class),
        );
    }

    private function runHeurekaTest(HeurekaFacade $heurekaFacade, bool $heurekaAgreement = true): void
    {
        $order = $this->createOrderMock($heurekaAgreement);
        $orderFacade = $this->createOrderFacade($heurekaFacade, $order);
        $orderFacade->sendHeurekaOrderInfo($order->getId());
    }

    private function createDomain(): Domain
    {
        $domainConfig = DomainConfigHelper::getDomainConfig();
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        return new Domain(
            [$domainConfig],
            $this->createMock(Setting::class),
            $administratorFacadeMock,
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function createOrderMock(bool $heurekaAgreement): MockObject
    {
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);
        $order->method('isHeurekaAgreement')->willReturn($heurekaAgreement);

        return $order;
    }
}
