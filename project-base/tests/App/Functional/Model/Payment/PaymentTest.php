<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
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
