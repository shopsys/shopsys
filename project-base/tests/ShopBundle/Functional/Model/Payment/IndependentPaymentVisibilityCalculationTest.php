<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
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
     * @var \Shopsys\ShopBundle\Model\Payment\PaymentDataFactory
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
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => true,
        ];
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $em->persist($vat);
        $em->persist($payment);
        $em->flush();

        $this->assertTrue($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleEmptyName()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = null;
        }
        $paymentData->name = $names;
        $paymentData->vat = $vat;
        $paymentData->hidden = false;
        $paymentData->enabled = [
            self::FIRST_DOMAIN_ID => true,
            self::SECOND_DOMAIN_ID => false,
        ];

        $payment = new Payment($paymentData);

        $em->persist($vat);
        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleNotOnDomain()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $em->persist($vat);
        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    public function testIsIndependentlyVisibleHidden()
    {
        $em = $this->getEntityManager();
        $vat = $this->getDefaultVat();

        $enabledForDomains = [
            self::FIRST_DOMAIN_ID => false,
            self::SECOND_DOMAIN_ID => false,
        ];
        $payment = $this->getDefaultPayment($vat, $enabledForDomains, false);

        $em->persist($vat);
        $em->persist($payment);
        $em->flush();

        $this->assertFalse($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, self::FIRST_DOMAIN_ID));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param bool[] $enabledForDomains
     * @param bool $hidden
     * @return \Shopsys\ShopBundle\Model\Payment\Payment
     */
    public function getDefaultPayment(Vat $vat, $enabledForDomains, $hidden)
    {
        $paymentData = $this->paymentDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'paymentName';
        }
        $paymentData->name = $names;
        $paymentData->vat = $vat;
        $paymentData->hidden = $hidden;
        $paymentData->enabled = $enabledForDomains;

        return new Payment($paymentData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    private function getDefaultVat()
    {
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        return new Vat($vatData);
    }
}
