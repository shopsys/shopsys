<?php

namespace Tests\ShopBundle\Database\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\ShopBundle\Test\DatabaseTestCase;

class PaymentTest extends DatabaseTestCase
{
    public function testRemoveTransportFromPaymentAfterDelete()
    {
        $paymentDataFactory = $this->getContainer()->get(PaymentDataFactory::class);
        $transportDataFactory = $this->getContainer()->get(TransportDataFactory::class);
        $em = $this->getEntityManager();
        $currencyFacade = $this->getContainer()->get(CurrencyFacade::class);
        $paymentPriceFactory = $this->getContainer()->get(PaymentPriceFactory::class);

        $vat = new Vat(new VatData('vat', 21));
        $transportData = $transportDataFactory->createDefault();
        $transportData->name['cs'] = 'name';
        $transportData->vat = $vat;
        $transport = new Transport($transportData);

        $paymentData = $paymentDataFactory->createDefault();
        $paymentData->name['cs'] = 'name';
        $paymentData->vat = $vat;

        $payment = new Payment(
            $paymentData,
            $currencyFacade->getAll(),
            $paymentPriceFactory
        );
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $transportFacade->deleteById($transport->getId());

        $this->assertFalse($payment->getTransports()->contains($transport));
    }
}
