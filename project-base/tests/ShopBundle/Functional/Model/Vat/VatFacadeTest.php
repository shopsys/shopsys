<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Vat;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\ShopBundle\DataFixtures\Demo\PaymentDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\TransportDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\VatDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class VatFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     * @inject
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     * @inject
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    private $paymentFacade;

    public function testDeleteByIdAndReplaceForFirstDomain()
    {
        $em = $this->getEntityManager();

        $vatData = new VatData();
        $vatData->name = 'name';
        $vatData->percent = '10';
        $vatToDelete = $this->vatFacade->create($vatData, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatToReplaceWith */
        $vatToReplaceWith = $this->getReference(sprintf('%s_%s', VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID));
        /** @var \Shopsys\ShopBundle\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $transportData = $this->transportDataFactory->createFromTransport($transport);
        /** @var \Shopsys\ShopBundle\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);

        $transportData->vat = $vatToDelete;
        $this->transportFacade->edit($transport, $transportData);

        $paymentData->vat = $vatToDelete;
        $this->paymentFacade->edit($payment, $paymentData);

        $this->vatFacade->deleteByIdAndUpdateDefaultVatForDomain($vatToDelete, Domain::FIRST_DOMAIN_ID, $vatToReplaceWith);

        $em->refresh($transport);
        $em->refresh($payment);

        $this->assertEquals($vatToReplaceWith, $transport->getVat());
        $this->assertEquals($vatToReplaceWith, $payment->getVat());
    }
}
