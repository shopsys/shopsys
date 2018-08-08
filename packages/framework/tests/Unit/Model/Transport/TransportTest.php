<?php

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;

class TransportTest extends TestCase
{
    private function createTransport(): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        $vat = new Vat($vatData);
        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->vat = $vat;
        $transportData->hidden = false;
        $transport = new Transport($transportData);

        return $transport;
    }

    private function createPayment(): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        $vat = new Vat($vatData);
        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName', 'en' => 'paymentName'];
        $paymentData->vat = $vat;
        $paymentData->hidden = true;
        $payment = new Payment($paymentData);

        return $payment;
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
