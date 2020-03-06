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
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
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
        $em = $this->getEntityManager();

        $transportData = $this->transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transport = new Transport($transportData);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->name['cs'] = 'name';

        $payment = new Payment($paymentData);
        $payment->addTransport($transport);

        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $this->transportFacade->deleteById($transport->getId());

        $this->assertNotContains($transport, $payment->getTransports());
    }
}
