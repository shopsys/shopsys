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
         * The "EUR" currency is created in database migration.
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135342
         */
        $currencyEur = $this->currencyFacade->getById(1);
        $this->addReference(self::CURRENCY_EUR, $currencyEur);

        $currencyData = $this->currencyDataFactory->create();
        $currencyData->name = 'Česká koruna';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '0.04';
        $currencyCzk = $this->currencyFacade->create($currencyData);
        $this->addReference(self::CURRENCY_CZK, $currencyCzk);

        $this->currencyFacade->setDefaultCurrency($currencyEur);

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId === 2) {
                $this->currencyFacade->setDomainDefaultCurrency($currencyCzk, $domainId);
            } else {
                $this->currencyFacade->setDomainDefaultCurrency($currencyEur, $domainId);
            }
        }
    }
}
