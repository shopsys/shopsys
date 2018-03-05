<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyDataFixture extends AbstractReferenceFixture
{
    const CURRENCY_CZK = 'currency_czk';
    const CURRENCY_EUR = 'currency_eur';

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $currencyData = new CurrencyData();

        $currencyData->name = 'Česká koruna';
        $currencyData->code = Currency::CODE_CZK;
        $this->createCurrency($currencyData, self::CURRENCY_CZK);

        $currencyData->name = 'Euro';
        $currencyData->code = Currency::CODE_EUR;
        $currencyData->exchangeRate = 25;
        $this->createCurrency($currencyData, self::CURRENCY_EUR);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @param string|null $referenceName
     */
    private function createCurrency(CurrencyData $currencyData, $referenceName = null)
    {
        $currencyFacade = $this->get(CurrencyFacade::class);
        /* @var $currencyFacade \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade */

        $currency = $currencyFacade->create($currencyData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $currency);
        }
    }
}
