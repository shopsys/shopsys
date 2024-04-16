<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
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
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private function createOrderFacade(HeurekaFacade $heurekaFacade, Order $order): OrderFacade
    {
        $orderRepositoryMock = $this->createMock(OrderRepository::class);
        $orderRepositoryMock->method('getById')->willReturn($order);

        return new OrderFacade(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(OrderNumberSequenceRepository::class),
            $orderRepositoryMock,
            $this->createMock(OrderUrlGenerator::class),
            $this->createMock(OrderStatusRepository::class),
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
            $this->createMock(OrderFactoryInterface::class),
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
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param bool $heurekaAgreement
     */
    private function runHeurekaTest(HeurekaFacade $heurekaFacade, bool $heurekaAgreement = true): void
    {
        $order = $this->createOrderMock($heurekaAgreement);
        $orderFacade = $this->createOrderFacade($heurekaFacade, $order);
        $orderFacade->sendHeurekaOrderInfo($order->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function createDomain(): Domain
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig = new DomainConfig(Domain::FIRST_DOMAIN_ID, '', '', 'cs', $defaultTimeZone);

        return new Domain([$domainConfig], $this->createMock(Setting::class));
    }

    /**
     * @param bool $heurekaAgreement
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
