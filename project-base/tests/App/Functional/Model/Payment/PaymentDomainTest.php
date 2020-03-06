<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class PaymentDomainTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface
     * @inject
     */
    private $paymentFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
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
        if (count($this->domain->getAllIds()) === 1) {
            $this->markTestSkipped('Test is skipped for single domain');
        }

        $paymentData = $this->paymentDataFactory->create();

        $paymentData->enabled[self::FIRST_DOMAIN_ID] = true;
        $paymentData->enabled[self::SECOND_DOMAIN_ID] = false;

        $payment = $this->paymentFactory->create($paymentData);

        $refreshedPayment = $this->getRefreshedPaymentFromDatabase($payment);

        $this->assertTrue($refreshedPayment->isEnabled(self::FIRST_DOMAIN_ID));
        $this->assertFalse($refreshedPayment->isEnabled(self::SECOND_DOMAIN_ID));
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Payment\Payment
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
