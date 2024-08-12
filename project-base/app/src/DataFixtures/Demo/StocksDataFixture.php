<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
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
    private const array ENABLED_FIRST_DOMAIN = [
        1 => true,
        2 => false,
    ];
    private const array ENABLED_SECOND_DOMAIN = [
        1 => false,
        2 => true,
    ];
    public const string STOCK_PREFIX = 'stock_';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockDataFactory $stockDataFactory
     */
    public function __construct(
        private readonly StockFacade $stockFacade,
        private readonly StockDataFactory $stockDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDemoData() as $demoRow) {
            $stock = $this->stockFacade->create($this->initStockData($demoRow));
            $this->addReference(self::STOCK_PREFIX . $stock->getId(), $stock);
        }
    }

    /**
     * @param array $demoRow
     * @return \Shopsys\FrameworkBundle\Model\Stock\StockData
     */
    protected function initStockData(array $demoRow): StockData
    {
        $stockData = $this->stockDataFactory->create();

        $stockData->name = $demoRow[self::ATTR_NAME];
        $stockData->externalId = $demoRow[self::ATTR_EXTERNAL];
        $stockData->isDefault = $demoRow[self::ATTR_IS_DEFAULT];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $stockData->isEnabledByDomain[$domainId] = $demoRow[self::ATTR_ENABLED_BY_DOMAIN][$domainId] ?? false;
        }
        $stockData->note = $demoRow[self::ATTR_NOTE];

        return $stockData;
    }

    /**
     * @return array
     */
    private function getDemoData(): array
    {
        return [
            [
                self::ATTR_NAME => 'Praha - Stodůlky',
                self::ATTR_EXTERNAL => '701-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Modřice u Brna',
                self::ATTR_EXTERNAL => '704-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Central warehouse',
                self::ATTR_EXTERNAL => '800-cz',
                self::ATTR_IS_DEFAULT => true,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Ostrava - Mariánské Hory',
                self::ATTR_EXTERNAL => '702-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Praha - Černý most',
                self::ATTR_EXTERNAL => '703-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Liberec',
                self::ATTR_EXTERNAL => '705-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Ústí nad Labem',
                self::ATTR_EXTERNAL => '706-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'České Budějovice',
                self::ATTR_EXTERNAL => '707-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Hradec Králové',
                self::ATTR_EXTERNAL => '708-cz',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_FIRST_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Central warehouse SK',
                self::ATTR_EXTERNAL => '801-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => null,
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_SECOND_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Trnava',
                self::ATTR_EXTERNAL => '731-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Entry on the right',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_SECOND_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Nitra',
                self::ATTR_EXTERNAL => '732-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => null,
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_SECOND_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Bratislava',
                self::ATTR_EXTERNAL => '733-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Key is under the mat',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_SECOND_DOMAIN,
            ],
            [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_EXTERNAL => '734-sk',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_NOTE => 'Shortened opening hours',
                self::ATTR_ENABLED_BY_DOMAIN => self::ENABLED_SECOND_DOMAIN,
            ],
        ];
    }
}
