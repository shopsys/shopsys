<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Vat;

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
     * @var \Shopsys\ShopBundle\Model\Transport\TransportDataFactory
     * @inject
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\ShopBundle\Model\Payment\PaymentDataFactory
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    private $paymentFacade;

    public function testDeleteByIdAndReplace()
    {
        $em = $this->getEntityManager();

        $vatData = new VatData();
        $vatData->name = 'name';
        $vatData->percent = '10';
        $vatToDelete = $this->vatFacade->create($vatData);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatToReplaceWith */
        $vatToReplaceWith = $this->getReference(VatDataFixture::VAT_HIGH);
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

        $this->vatFacade->deleteById($vatToDelete, $vatToReplaceWith);

        $em->refresh($transport);
        $em->refresh($payment);

        $this->assertEquals($vatToReplaceWith, $transport->getVat());
        $this->assertEquals($vatToReplaceWith, $payment->getVat());
    }
}
