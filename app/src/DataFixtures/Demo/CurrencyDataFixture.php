<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CurrencyDataFixture extends AbstractReferenceFixture
{
    public const CURRENCY_CZK = 'currency_czk';
    public const CURRENCY_EUR = 'currency_eur';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        readonly private CurrencyFacade $currencyFacade
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * The "CZK" and "EUR" currencies are created in database migrations.
         *
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135342
         */
        $currencyCzk = $this->currencyFacade->getById(1);
        $this->addReference(self::CURRENCY_CZK, $currencyCzk);

        $currencyEur = $this->currencyFacade->getById(2);
        $this->addReference(self::CURRENCY_EUR, $currencyEur);
    }
}
