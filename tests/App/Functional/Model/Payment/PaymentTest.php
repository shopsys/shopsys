<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Payment\PaymentData;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportData;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class PaymentTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \App\Model\Payment\PaymentDataFactory
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     * @inject
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     * @inject
     */
    private $transportFacade;

    public function testRemoveTransportFromPaymentAfterDelete()
    {
        /** @var TransportData $transportData */
        $transportData = $this->transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transportData->externalId = $this->getNextTransportExternalId();
        $transportData->deliveryCode = 'A';
        $transportData->typeOfDeliveryKey = 1;
        $transport = new Transport($transportData);

        /** @var PaymentData $paymentData */
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->name['cs'] = 'name';
        $paymentData->externalId = $this->getNextPaymentExternalId();
        $paymentData->meanOfPayment = 'A';

        $payment = new Payment($paymentData);
        $payment->addTransport($transport);

        $this->em->persist($transport);
        $this->em->persist($payment);
        $this->em->flush();

        $this->transportFacade->deleteById($transport->getId());

        $this->assertNotContains($transport, $payment->getTransports());
    }
}
