<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;

class TransportVisibilityCalculationTest extends TestCase
{
    public function testIsVisibleWhenIndepentlyInvisible(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(false);

        $independentPaymentVisibilityCalculationStub = $this
            ->createStub(IndependentPaymentVisibilityCalculation::class);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationStub,
            $entityManagerStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [], $domainId));
    }

    public function testIsVisibleWithHiddenPayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentStub = $this->createStub(Payment::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentStub), $this->equalTo($domainId))
            ->willReturn(false);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $entityManagerStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [$paymentStub], $domainId));
    }

    public function testIsVisibleWithoutPayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $entityManagerStub,
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportStub, [$paymentMock], $domainId));
    }

    public function testIsVisibleWithVisiblePayment(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportStub]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $entityManagerStub,
        );

        $this->assertTrue($transportVisibilityCalculation->isVisible($transportStub, [$paymentMock], $domainId));
    }

    public function testFilterVisible(): void
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportHiddenStub = $this->createStub(Transport::class);
        $transportVisibleStub = $this->createStub(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportVisibleStub]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(
            IndependentTransportVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportVisibleStub), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(
            IndependentPaymentVisibilityCalculation::class,
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $entityManagerStub = $this->createStub(EntityManagerInterface::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock,
            $entityManagerStub,
        );

        $transports = [$transportHiddenStub, $transportVisibleStub];

        $filteredTransports = $transportVisibilityCalculation->filterVisible($transports, [$paymentMock], $domainId);

        $this->assertCount(1, $filteredTransports);
        $this->assertContains($transportVisibleStub, $filteredTransports);
    }
}
