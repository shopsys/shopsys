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
    private function createTransport()
    {
        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->hidden = false;
        $transport = new Transport($transportData);

        return $transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    private function createPayment()
    {
        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName', 'en' => 'paymentName'];
        $paymentData->hidden = true;
        $payment = new Payment($paymentData);

        return $payment;
    }

    public function testSetPayments()
    {
        $transport = $this->createTransport();
        $payment = $this->createPayment();
        $transport->setPayments([$payment]);

        $this->assertContains($payment, $transport->getPayments());
        $this->assertContains($transport, $payment->getTransports());
    }

    public function testRemovePayment()
    {
        $transport = $this->createTransport();
        $payment = $this->createPayment();
        $transport->setPayments([$payment]);
        $transport->removePayment($payment);

        $this->assertNotContains($payment, $transport->getPayments());
        $this->assertNotContains($transport, $payment->getTransports());
    }
}
