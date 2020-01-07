<?php

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;

class TransportVisibilityCalculationTest extends TestCase
{
    public function testIsVisibleWhenIndepentlyInvisible()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportMock = $this->createMock(Transport::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(IndependentTransportVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportMock), $this->equalTo($domainId))
            ->willReturn(false);

        $independentPaymentVisibilityCalculationMock = $this
            ->createMock(IndependentPaymentVisibilityCalculation::class);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportMock, [], $domainId));
    }

    public function testIsVisibleWithHiddenPayment()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportMock = $this->createMock(Transport::class);
        $paymentMock = $this->createMock(Payment::class);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(IndependentTransportVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportMock), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(IndependentPaymentVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(false);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportMock, [$paymentMock], $domainId));
    }

    public function testIsVisibleWithoutPayment()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportMock = $this->createMock(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(IndependentTransportVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportMock), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(IndependentPaymentVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock
        );

        $this->assertFalse($transportVisibilityCalculation->isVisible($transportMock, [$paymentMock], $domainId));
    }

    public function testIsVisibleWithVisiblePayment()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportMock = $this->createMock(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportMock]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(IndependentTransportVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportMock), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(IndependentPaymentVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock
        );

        $this->assertTrue($transportVisibilityCalculation->isVisible($transportMock, [$paymentMock], $domainId));
    }

    public function testFilterVisible()
    {
        $domainId = Domain::FIRST_DOMAIN_ID;
        $transportHiddenMock = $this->createMock(Transport::class);
        $transportVisibleMock = $this->createMock(Transport::class);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTransports'])
            ->getMock();
        $paymentMock->expects($this->atLeastOnce())->method('getTransports')->willReturn([$transportVisibleMock]);

        $independentTransportVisibilityCalculationMock = $this->getMockBuilder(IndependentTransportVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentTransportVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($transportVisibleMock), $this->equalTo($domainId))
            ->willReturn(true);

        $independentPaymentVisibilityCalculationMock = $this->getMockBuilder(IndependentPaymentVisibilityCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIndependentlyVisible'])
            ->getMock();
        $independentPaymentVisibilityCalculationMock
            ->expects($this->atLeastOnce())
            ->method('isIndependentlyVisible')
            ->with($this->equalTo($paymentMock), $this->equalTo($domainId))
            ->willReturn(true);

        $transportVisibilityCalculation = new TransportVisibilityCalculation(
            $independentTransportVisibilityCalculationMock,
            $independentPaymentVisibilityCalculationMock
        );

        $transports = [$transportHiddenMock, $transportVisibleMock];

        $filteredTransports = $transportVisibilityCalculation->filterVisible($transports, [$paymentMock], $domainId);

        $this->assertCount(1, $filteredTransports);
        $this->assertContains($transportVisibleMock, $filteredTransports);
    }
}
