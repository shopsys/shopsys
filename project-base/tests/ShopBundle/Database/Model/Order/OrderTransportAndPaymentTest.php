<?php

namespace Tests\ShopBundle\Database\Model\Order;

use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\ShopBundle\Test\DatabaseTestCase;

class OrderTransportAndPaymentTest extends DatabaseTestCase
{
//    public function testVisibleTransport()
//    {
//        $em = $this->getEntityManager();
//        $vat = $this->getDefaultVat();
//
//        $enabledForDomains = [
//            1 => true,
//            2 => false,
//        ];
//        $transport = $this->getDefaultTransport($vat, $enabledForDomains, false);
//        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);
//
//        $payment->addTransport($transport);
//
//        $em->persist($vat);
//        $em->persist($transport);
//        $em->flush();
//        $em->persist($payment);
//        $em->flush();
//
//        $transportFacade = $this->getContainer()->get(TransportFacade::class);
//        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
//        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
//        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */
//
//        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
//        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);
//
//        $this->assertContains($transport, $visibleTransports);
    //    }
    public function testVisibleTransportHiddenTransport(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledOnDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $enabledOnDomains, true);
        $payment = $this->getDefaultPayment($vat, $enabledOnDomains, false);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportHiddenPayment(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => false,
        ];

        $transport = $this->getDefaultTransport($vat, $transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $paymentEnabledForDomains, true);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportNoPayment(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $enabledForDomains, false);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportOnDifferentDomain(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $paymentEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transportEnabledForDomains = [
            1 => false,
            2 => true,
        ];

        $transport = $this->getDefaultTransport($vat, $transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $paymentEnabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportPaymentOnDifferentDomain(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $paymentEnabledForDomains, false);
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisiblePayment(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $enabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenTransport(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $enabledForDomains, true);
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenPayment(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($vat, $enabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, true);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentNoTransport(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $em->persist($vat);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentOnDifferentDomain(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transport = $this->getDefaultTransport($vat, $transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $paymentEnabledForDomains, false);
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentTransportOnDifferentDomain(): void
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transport = $this->getDefaultTransport($vat, $transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($vat, $paymentEnabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     */
    public function getDefaultPayment(Vat $vat, $enabledForDomains, $hidden): Payment
    {
        $paymentDataFactory = $this->getPaymentDataFactory();

        $paymentData = $paymentDataFactory->create();
        $paymentData->name = [
            'cs' => 'paymentName',
            'en' => 'paymentName',
        ];
        $paymentData->vat = $vat;
        $paymentData->hidden = $hidden;
        $paymentData->enabled = $enabledForDomains;

        return new Payment($paymentData);
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     */
    public function getDefaultTransport(Vat $vat, $enabledForDomains, $hidden): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        $transportDataFactory = $this->getTransportDataFactory();

        $transportData = $transportDataFactory->create();
        $transportData->name = [
            'cs' => 'paymentName',
            'en' => 'paymentName',
        ];

        $transportData->vat = $vat;
        $transportData->hidden = $hidden;
        $transportData->enabled = $enabledForDomains;

        return new Transport($transportData);
    }

    private function getDefaultVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        return new Vat($vatData);
    }

    public function getPaymentDataFactory(): \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory
    {
        return $this->getContainer()->get(PaymentDataFactory::class);
    }

    public function getTransportDataFactory(): \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory
    {
        return $this->getContainer()->get(TransportDataFactory::class);
    }
}
