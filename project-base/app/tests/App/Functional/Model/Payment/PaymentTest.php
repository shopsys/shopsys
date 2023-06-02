<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class PaymentTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    /**
     * @inject
     */
    private TransportDataFactory $transportDataFactory;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    public function testRemoveTransportFromPaymentAfterDelete()
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transport = new Transport($transportData);

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
