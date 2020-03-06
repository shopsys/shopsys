<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class IndependentPaymentVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation
     * @inject
     */
    private $independentPaymentVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     * @inject
     */
    private $localization;

    public function testIsIndependentlyVisible()
    {
        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => true,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertTrue($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $paymentData->name = $names;
        $paymentData->hidden = false;
        $paymentData->enabled = $this->getFilteredEnabledForDomains([
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => false,
        ]);

        $payment = new Payment($paymentData);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @return \App\Model\Payment\Payment
     */
    public function getDefaultPayment($enabledForDomains, $hidden)
    {
        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'paymentName';
        }
        $paymentData->name = $names;
        $paymentData->hidden = $hidden;
        $paymentData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);

        return new Payment($paymentData);
    }

    /**
     * @param bool[] $enabledForDomains
     * @return bool[]
     */
    private function getFilteredEnabledForDomains(array $enabledForDomains): array
    {
        return array_intersect_key($enabledForDomains, array_flip($this->domain->getAllIds()));
    }
}
