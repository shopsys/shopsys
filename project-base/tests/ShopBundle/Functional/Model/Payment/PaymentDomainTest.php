<?php

namespace Tests\ShopBundle\Functional\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface;
use Shopsys\ShopBundle\Model\Payment\Payment;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class PaymentDomainTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\ShopBundle\Model\Payment\PaymentDataFactory
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    public function setUp()
    {
        parent::setUp();
        $this->paymentDataFactory = $this->getContainer()->get(PaymentDataFactoryInterface::class);
        $this->paymentFactory = $this->getContainer()->get(PaymentFactoryInterface::class);
        $this->em = $this->getEntityManager();
    }

    public function testCreatePaymentEnabledOnDomain()
    {
        $paymentData = $this->paymentDataFactory->create();

        $paymentData->enabled = [
            self::FIRST_DOMAIN_ID => true,
        ];

        $payment = $this->paymentFactory->create($paymentData);

        $refreshedPayment = $this->getRefreshedPaymentFromDatabase($payment);

        $this->assertTrue($refreshedPayment->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreatePaymentDisabledOnDomain()
    {
        $paymentData = $this->paymentDataFactory->create();

        $paymentData->enabled[self::FIRST_DOMAIN_ID] = false;

        $payment = $this->paymentFactory->create($paymentData);

        $refreshedPayment = $this->getRefreshedPaymentFromDatabase($payment);

        $this->assertFalse($refreshedPayment->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreatePaymentWithDifferentVisibilityOnDomains()
    {
        $paymentData = $this->paymentDataFactory->create();

        $paymentData->enabled[self::FIRST_DOMAIN_ID] = true;
        $paymentData->enabled[self::SECOND_DOMAIN_ID] = false;

        $payment = $this->paymentFactory->create($paymentData);

        $refreshedPayment = $this->getRefreshedPaymentFromDatabase($payment);

        $this->assertTrue($refreshedPayment->isEnabled(self::FIRST_DOMAIN_ID));
        $this->assertFalse($refreshedPayment->isEnabled(self::SECOND_DOMAIN_ID));
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Payment\Payment $payment
     * @return \Shopsys\ShopBundle\Model\Payment\Payment
     */
    private function getRefreshedPaymentFromDatabase(Payment $payment)
    {
        $this->em->persist($payment);
        $this->em->flush();

        $paymentId = $payment->getId();

        $this->em->clear();

        return $this->em->getRepository(Payment::class)->find($paymentId);
    }
}
