<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyDataFixture extends AbstractReferenceFixture
{
    public const CURRENCY_CZK = 'currency_czk';
    public const CURRENCY_EUR = 'currency_eur';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface
     */
    protected $currencyDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface $currencyDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        CurrencyDataFactoryInterface $currencyDataFactory,
        Domain $domain
    ) {
        $this->currencyFacade = $currencyFacade;
        $this->currencyDataFactory = $currencyDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * The "CZK" currency is created in database migration.
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135342
         */
        $currencyCzk = $this->currencyFacade->getById(1);
        $currencyData = $this->currencyDataFactory->createFromCurrency($currencyCzk);
        $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currencyCzk = $this->currencyFacade->edit($currencyCzk->getId(), $currencyData);
        $this->addReference(self::CURRENCY_CZK, $currencyCzk);

        if (count($this->domain->getAll()) > 1) {
            $currencyData = $this->currencyDataFactory->create();
            $currencyData->name = 'Euro';
            $currencyData->code = Currency::CODE_EUR;
            $currencyData->exchangeRate = '25';
            $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
            $currencyData->roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS;
            $currencyEuro = $this->currencyFacade->create($currencyData);
            $this->addReference(self::CURRENCY_EUR, $currencyEuro);
        }
    }
}
