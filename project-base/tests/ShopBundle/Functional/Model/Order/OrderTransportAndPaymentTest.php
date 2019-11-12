<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Payment\Payment;
use Shopsys\ShopBundle\Model\Transport\Transport;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class OrderTransportAndPaymentTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     * @inject
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     * @inject
     */
    private $transportDataFactory;

    public function testVisibleTransport()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            Domain::FIRST_DOMAIN_ID => true,
            Domain::SECOND_DOMAIN_ID => false,
        ];
        $transport = $this->getDefaultTransport($enabledForDomains, false);
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertContains($transport, $visibleTransports);
    }

    public function testVisibleTransportHiddenTransport()
    {
        $em = $this->getEntityManager();

        $enabledOnDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($enabledOnDomains, true);
        $payment = $this->getDefaultPayment($enabledOnDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportHiddenPayment()
    {
        $em = $this->getEntityManager();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => false,
        ];

        $transport = $this->getDefaultTransport($transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($paymentEnabledForDomains, true);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportNoPayment()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($enabledForDomains, false);

        $em->persist($transport);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $paymentEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transportEnabledForDomains = [
            1 => false,
            2 => true,
        ];

        $transport = $this->getDefaultTransport($transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($paymentEnabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportPaymentOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($paymentEnabledForDomains, false);
        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $this->transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisiblePayment()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($enabledForDomains, false);
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenTransport()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($enabledForDomains, true);
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenPayment()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $transport = $this->getDefaultTransport($enabledForDomains, false);
        $payment = $this->getDefaultPayment($enabledForDomains, true);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentNoTransport()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            1 => true,
            2 => false,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transport = $this->getDefaultTransport($transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($paymentEnabledForDomains, false);
        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentTransportOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $transportEnabledForDomains = [
            1 => true,
            2 => false,
        ];
        $paymentEnabledForDomains = [
            1 => false,
            2 => true,
        ];
        $transport = $this->getDefaultTransport($transportEnabledForDomains, false);
        $payment = $this->getDefaultPayment($paymentEnabledForDomains, false);

        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $visiblePayments = $this->paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Payment\Payment
     */
    public function getDefaultPayment($enabledForDomains, $hidden)
    {
        $paymentDataFactory = $this->paymentDataFactory;

        $paymentData = $paymentDataFactory->create();
        $names = [];
        foreach ($this->domain->getAllLocales() as $locale) {
            $names[$locale] = 'paymentName';
        }
        $paymentData->name = $names;
        $paymentData->hidden = $hidden;
        $paymentData->enabled = $enabledForDomains;

        return new Payment($paymentData);
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Transport\Transport
     */
    public function getDefaultTransport($enabledForDomains, $hidden)
    {
        $transportDataFactory = $this->transportDataFactory;

        $transportData = $transportDataFactory->create();
        $names = [];
        foreach ($this->domain->getAllLocales() as $locale) {
            $names[$locale] = 'transportName';
        }
        $transportData->name = $names;

        $transportData->hidden = $hidden;
        $transportData->enabled = $enabledForDomains;

        return new Transport($transportData);
    }
}
