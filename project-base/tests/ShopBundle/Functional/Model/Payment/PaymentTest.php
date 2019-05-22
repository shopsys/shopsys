<?php

namespace Tests\ShopBundle\Functional\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\ShopBundle\Model\Payment\Payment;
use Shopsys\ShopBundle\Model\Transport\Transport;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class PaymentTest extends TransactionFunctionalTestCase
{
    public function testRemoveTransportFromPaymentAfterDelete()
    {
        /** @var \Shopsys\ShopBundle\Model\Payment\PaymentDataFactory $paymentDataFactory */
        $paymentDataFactory = $this->getContainer()->get(PaymentDataFactoryInterface::class);
        /** @var \Shopsys\ShopBundle\Model\Transport\TransportDataFactory $transportDataFactory */
        $transportDataFactory = $this->getContainer()->get(TransportDataFactoryInterface::class);
        $em = $this->getEntityManager();

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData);
        $transportData = $transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transportData->vat = $vat;
        $transport = new Transport($transportData);

        $paymentData = $paymentDataFactory->create();
        $paymentData->name['cs'] = 'name';
        $paymentData->vat = $vat;

        $payment = new Payment($paymentData);
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade */
        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        $transportFacade->deleteById($transport->getId());

        $this->assertFalse($payment->getTransports()->contains($transport));
    }
}
