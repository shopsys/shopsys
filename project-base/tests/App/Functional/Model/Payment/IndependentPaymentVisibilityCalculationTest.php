<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class IndependentPaymentVisibilityCalculationTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @inject
     */
    private IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    /**
     * @inject
     */
    private Localization $localization;

    public function testIsIndependentlyVisible()
    {
        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => true,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertTrue(
            $this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID),
        );
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

        $this->assertFalse(
            $this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID),
        );
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

        $this->assertFalse(
            $this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID),
        );
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

        $this->assertFalse(
            $this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID),
        );
    }

    public function testIsNotIndependentlyVisibleWhenDeleted(): void
    {
        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => true,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false, true);

        $this->em->persist($payment);
        $this->em->flush();

        $this->assertFalse(
            $this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID),
        );
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @param bool $deleted
     * @return \App\Model\Payment\Payment
     */
    public function getDefaultPayment($enabledForDomains, $hidden, bool $deleted = false)
    {
        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'paymentName';
        }
        $paymentData->name = $names;
        $paymentData->hidden = $hidden;
        $paymentData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);

        $payment = new Payment($paymentData);

        if ($deleted) {
            $payment->markAsDeleted();
        }

        return $payment;
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
