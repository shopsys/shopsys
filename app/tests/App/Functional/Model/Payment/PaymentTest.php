<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\App\Test\FunctionalTestCase;

class PaymentTest extends FunctionalTestCase
{
    /**
     * @var \App\Model\Payment\PaymentDataFactory
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     * @inject
     */
    private TransportFacade $transportFacade;

    public function testRemoveTransportFromPaymentAfterDelete()
    {
        /** @var \App\Model\Transport\TransportData $transportData */
        $transportData = $this->transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transportData->deliveryCode = 'A';
        $transportData->typeOfDeliveryKey = 1;
        $transport = new Transport($transportData);

        /** @var \App\Model\Payment\PaymentData $paymentData */
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->name['cs'] = 'name';

        $payment = new Payment($paymentData);
        $payment->addTransport($transport);

        $this->em->persist($transport);
        $this->em->persist($payment);
        $this->em->flush();

        $this->transportFacade->deleteById($transport->getId());

        $this->assertNotContains($transport, $payment->getTransports());
    }
}
