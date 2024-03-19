<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Vat;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class VatFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @inject
     */
    private TransportDataFactoryInterface $transportDataFactory;

    /**
     * @inject
     */
    private PaymentDataFactoryInterface $paymentDataFactory;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    public function testDeleteByIdAndReplaceForFirstDomain()
    {
        $vatData = new VatData();
        $vatData->name = 'name';
        $vatData->percent = '10';
        $vatToDelete = $this->vatFacade->create($vatData, Domain::FIRST_DOMAIN_ID);

        $vatToReplaceWith = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID, Vat::class);

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);

        $transportData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->transportFacade->edit($transport, $transportData);

        $paymentData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->paymentFacade->edit($payment, $paymentData);

        $this->vatFacade->deleteById($vatToDelete->getId(), $vatToReplaceWith->getId());

        $this->em->refresh($transport->getTransportDomain(Domain::FIRST_DOMAIN_ID));
        $this->em->refresh($payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID));

        $this->assertEquals($vatToReplaceWith, $payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID)->getVat());
        $this->assertEquals($vatToReplaceWith, $transport->getTransportDomain(Domain::FIRST_DOMAIN_ID)->getVat());
    }
}
