<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Vat;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData;
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
    private TransportDataFactory $transportDataFactory;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testDeleteByIdAndReplaceForFirstDomain(): void
    {
        $vatData = new VatData();
        $vatData->name = 'name';
        $vatData->percent = '10';
        $vatToDelete = $this->vatFacade->create($vatData, Domain::FIRST_DOMAIN_ID);
        $vatToDeleteId = $vatToDelete->getId();

        $vatToReplaceWith = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID, Vat::class);

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH, Payment::class);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);

        $transportInputPricesData = new TransportInputPricesData();
        $transportInputPricesData->vat = $vatToDelete;
        $transportData->inputPricesByDomain[Domain::FIRST_DOMAIN_ID] = $transportInputPricesData;

        $this->transportFacade->edit($transport, $transportData);

        $paymentData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->paymentFacade->edit($payment, $paymentData);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->productInputPricesByDomain[Domain::FIRST_DOMAIN_ID]->vat = $vatToDelete;
        $this->productFacade->edit($product->getId(), $productData);

        $this->vatFacade->deleteById($vatToDeleteId, $vatToReplaceWith->getId());

        $this->em->refresh($transport->getTransportDomain(Domain::FIRST_DOMAIN_ID));
        $this->em->refresh($payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID));

        $this->assertEquals($vatToReplaceWith, $payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID)->getVat());
        $this->assertEquals($vatToReplaceWith, $transport->getTransportDomain(Domain::FIRST_DOMAIN_ID)->getVat());

        $this->em->clear();
        $refreshedProduct = $this->productFacade->getById($product->getId());
        $this->assertSame(
            $vatToReplaceWith->getId(),
            $refreshedProduct->getVatForDomain(Domain::FIRST_DOMAIN_ID)->getId(),
        );
        $this->expectException(VatNotFoundException::class);
        $this->vatFacade->getById($vatToDeleteId);
    }
}
