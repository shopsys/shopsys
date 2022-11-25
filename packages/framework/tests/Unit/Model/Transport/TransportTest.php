<?php

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;

class TransportTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    private function createTransport(): Transport
    {
        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->hidden = false;
        return new Transport($transportData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    private function createPayment(): Payment
    {
        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName', 'en' => 'paymentName'];
        $paymentData->hidden = true;
        return new Payment($paymentData);
    }

    public function testSetPayments(): void
    {
        $transport = $this->createTransport();
        $payment = $this->createPayment();
        $transport->setPayments([$payment]);

        $this->assertContains($payment, $transport->getPayments());
        $this->assertContains($transport, $payment->getTransports());
    }

    public function testRemovePayment(): void
    {
        $transport = $this->createTransport();
        $payment = $this->createPayment();
        $transport->setPayments([$payment]);
        $transport->removePayment($payment);

        $this->assertNotContains($payment, $transport->getPayments());
        $this->assertNotContains($transport, $payment->getTransports());
    }
}
