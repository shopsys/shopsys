<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Vat;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPrice;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;
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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     * @inject
     */
    private $currencyFacade;

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

        $transportData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->transportFacade->edit($transport, $transportData);

        $paymentData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->paymentFacade->edit($payment, $paymentData);

        $em->refresh($transport);
        $em->refresh($payment);

        $this->vatFacade->deleteByIdAndUpdateDefaultVatForDomain($vatToDelete, Domain::FIRST_DOMAIN_ID, $vatToReplaceWith);

        $defaultCurrencyForFirstDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);

        $transportVat = $em->createQueryBuilder()
            ->select('v')
            ->from(Vat::class, 'v')
            ->join(TransportPrice::class, 'tp', Join::WITH, 'v = tp.vat')
            ->where('tp.transport = :transport AND tp.currency = :currency AND tp.domainId = :domainId')
            ->setParameter('transport', $transport)
            ->setParameter('currency', $defaultCurrencyForFirstDomain)
            ->setParameter('domainId', Domain::FIRST_DOMAIN_ID)
            ->getQuery()->getSingleResult();

        $paymentVat = $em->createQueryBuilder()
            ->select('v')
            ->from(Vat::class, 'v')
            ->join(PaymentPrice::class, 'pp', Join::WITH, 'v = pp.vat')
            ->where('pp.payment = :payment AND pp.currency = :currency AND pp.domainId = :domainId')
            ->setParameter('payment', $payment)
            ->setParameter('currency', $defaultCurrencyForFirstDomain)
            ->setParameter('domainId', Domain::FIRST_DOMAIN_ID)
            ->getQuery()->getSingleResult();

        $this->assertEquals($vatToReplaceWith, $paymentVat);
        $this->assertEquals($vatToReplaceWith, $transportVat);
    }
}
