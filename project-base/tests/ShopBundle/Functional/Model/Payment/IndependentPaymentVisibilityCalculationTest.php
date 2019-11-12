<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Payment;

use Shopsys\ShopBundle\Model\Payment\Payment;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class IndependentPaymentVisibilityCalculationTest extends TransactionFunctionalTestCase
{
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
        $em = $this->getEntityManager();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => true,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $em->persist($payment);
        $em->flush();

        $this->assertTrue($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        $em = $this->getEntityManager();

        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $paymentData->name = $names;
        $paymentData->hidden = false;
        $paymentData->enabled = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => false,
        ];

        $payment = new Payment($paymentData);

        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $em = $this->getEntityManager();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($enabledForDomains, false);

        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    /**
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Payment\Payment
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
        $paymentData->enabled = $enabledForDomains;

        return new Payment($paymentData);
    }
}
