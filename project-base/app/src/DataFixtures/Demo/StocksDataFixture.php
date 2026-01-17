<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Stock\StockData;
use Shopsys\FrameworkBundle\Model\Stock\StockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

class StocksDataFixture extends AbstractReferenceFixture
{
    private const string ATTR_NAME = 'name';
    private const string ATTR_IS_DEFAULT = 'isDefault';
    private const string ATTR_NOTE = 'note';
    private const string ATTR_ENABLED_BY_DOMAIN = 'enabled';
    private const string ATTR_EXTERNAL = 'externalId';
    public const string STOCK_PREFIX = 'stock_';

    public function __construct(
        private readonly StockFacade $stockFacade,
        private readonly StockDataFactory $stockDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDemoData() as $demoRow) {
            $stock = $this->stockFacade->create($this->initStockData($demoRow));
            $this->addReference(self::STOCK_PREFIX . $stock->getId(), $stock);
        }
    }

    protected function initStockData(array $demoRow): StockData
    {
        $stockData = $this->stockDataFactory->create();

        $stockData->name = $demoRow[self::ATTR_NAME];
        $stockData->externalId = $demoRow[self::ATTR_EXTERNAL];
        $stockData->isDefault = $demoRow[self::ATTR_IS_DEFAULT];
        $stockData->isEnabledByDomain = $demoRow[self::ATTR_ENABLED_BY_DOMAIN] + $stockData->isEnabledByDomain;
        $stockData->note = $demoRow[self::ATTR_NOTE];

        return $stockData;
    }

    private function getDemoData(): array
    {
        $enabledOnFirstDomainOnly = $this->getDomainEnabledArray(true);
        $enabledOnAllDomainsExceptFirst = $this->getDomainEnabledArray(false);

        return [
            [
                self::ATTR_NAME => 'Praha - Stodůlky',
                self::ATTR_EXTERNAL => '701-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Modřice u Brna',
                self::ATTR_EXTERNAL => '704-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Central warehouse',
                self::ATTR_EXTERNAL => '800-cz',
                self::ATTR_IS_DEFAULT => true,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Ostrava - Mariánské Hory',
                self::ATTR_EXTERNAL => '702-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Praha - Černý most',
                self::ATTR_EXTERNAL => '703-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Liberec',
                self::ATTR_EXTERNAL => '705-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Ústí nad Labem',
                self::ATTR_EXTERNAL => '706-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'České Budějovice',
                self::ATTR_EXTERNAL => '707-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Hradec Králové',
                self::ATTR_EXTERNAL => '708-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnFirstDomainOnly,
            ],
            [
                self::ATTR_NAME => 'Central warehouse SK',
                self::ATTR_EXTERNAL => '801-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => null,
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnAllDomainsExceptFirst,
            ],
            [
                self::ATTR_NAME => 'Trnava',
                self::ATTR_EXTERNAL => '731-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Entry on the right',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnAllDomainsExceptFirst,
            ],
            [
                self::ATTR_NAME => 'Nitra',
                self::ATTR_EXTERNAL => '732-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => null,
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnAllDomainsExceptFirst,
            ],
            [
                self::ATTR_NAME => 'Bratislava',
                self::ATTR_EXTERNAL => '733-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Key is under the mat',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnAllDomainsExceptFirst,
            ],
            [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_EXTERNAL => '734-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Shortened opening hours',
                self::ATTR_ENABLED_BY_DOMAIN => $enabledOnAllDomainsExceptFirst,
            ],
        ];
    }

    /**
     * @return array<int, bool>
     */
    private function getDomainEnabledArray(bool $enabledOnFirstDomain): array
    {
        $domainEnabledArray = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            if ($domainId === Domain::FIRST_DOMAIN_ID) {
                $domainEnabledArray[$domainId] = $enabledOnFirstDomain;
            } else {
                $domainEnabledArray[$domainId] = !$enabledOnFirstDomain;
            }
        }

        return $domainEnabledArray;
    }
}
