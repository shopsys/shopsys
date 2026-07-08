<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
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
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
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
            $this->createStub(OrderNumberSequenceRepository::class),
            $orderRepositoryStub,
            $this->createStub(OrderUrlGenerator::class),
            $this->createStub(OrderStatusFacade::class),
            $this->createStub(OrderMailFacade::class),
            $this->createStub(OrderHashGeneratorRepository::class),
            $this->createStub(Setting::class),
            $this->createStub(Localization::class),
            $this->createStub(AdministratorFrontSecurityFacade::class),
            $this->createStub(CurrentPromoCodeFacade::class),
            $this->createStub(CartFacade::class),
            $this->createStub(CustomerUserFacade::class),
            $this->createStub(CurrentCustomerUser::class),
            $heurekaFacade,
            $this->createDomain(),
            $this->createStub(OrderFactory::class),
            $this->createStub(OrderPriceCalculation::class),
            $this->createStub(OrderItemPriceCalculation::class),
            $this->createStub(NumberFormatterExtension::class),
            $this->createStub(PaymentPriceCalculation::class),
            $this->createStub(CurrencyFacade::class),
            $this->createStub(TransportPriceCalculation::class),
            $this->createStub(OrderItemFactory::class),
            $this->createStub(PaymentTransactionFacade::class),
            $this->createStub(PaymentTransactionDataFactory::class),
            $this->createStub(PaymentServiceFacade::class),
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
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        return new Domain(
            [$domainConfig],
            $this->createStub(Setting::class),
            $currentAdministratorStub,
        );
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
