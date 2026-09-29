<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Locale\LocaleHelper;
use Shopsys\FrameworkBundle\Model\Stock\StockData;
use Shopsys\FrameworkBundle\Model\Stock\StockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;

class StocksDataFixture extends AbstractReferenceFixture
{
    private const string ATTR_NAME = 'name';
    private const string ATTR_EXTERNAL = 'externalId';
    private const string ATTR_NOTE = 'note';
    private const string ATTR_REFERENCE_NAME = 'referenceName';

    public const string STOCK_CENTRAL_CZ = 'stock_central_cz';
    public const string STOCK_OSTRAVA = 'stock_ostrava';
    public const string STOCK_PARDUBICE = 'stock_pardubice';
    public const string STOCK_BRNO = 'stock_brno';
    public const string STOCK_PRAHA = 'stock_praha';
    public const string STOCK_HRADEC_KRALOVE = 'stock_hradec_kralove';
    public const string STOCK_OLOMOUC = 'stock_olomouc';
    public const string STOCK_LIBEREC = 'stock_liberec';
    public const string STOCK_CENTRAL_SK = 'stock_central_sk';
    public const string STOCK_ZILINA = 'stock_zilina';
    public const string STOCK_BRATISLAVA = 'stock_bratislava';
    public const string STOCK_BANSKA_BYSTRICA = 'stock_banska_bystrica';

    private const array CENTRAL_STOCK_REFERENCES = [self::STOCK_CENTRAL_CZ, self::STOCK_CENTRAL_SK];

    public function __construct(
        private readonly StockFacade $stockFacade,
        private readonly StockDataFactory $stockDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $skDomainIds = [];
        $defaultDomainIds = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if ($domainConfig->getLocale() === LocaleHelper::LOCALE_SK) {
                $skDomainIds[] = $domainConfig->getId();
            } else {
                $defaultDomainIds[] = $domainConfig->getId();
            }
        }

        $this->createStocks($this->getDefaultDemoData(), $defaultDomainIds);
        $this->createStocks($this->getSkDemoData(), $skDomainIds);
    }

    /**
     * @param int[] $enabledDomainIds
     */
    private function createStocks(array $demoData, array $enabledDomainIds): void
    {
        if (count($enabledDomainIds) === 0) {
            return;
        }

        foreach ($demoData as $demoRow) {
            $stock = $this->stockFacade->create($this->initStockData($demoRow, $enabledDomainIds));
            $this->addReference($demoRow[self::ATTR_REFERENCE_NAME], $stock);
        }
    }

    /**
     * @param int[] $enabledDomainIds
     */
    protected function initStockData(array $demoRow, array $enabledDomainIds): StockData
    {
        $stockData = $this->stockDataFactory->create();

        $stockData->name = $demoRow[self::ATTR_NAME];
        $stockData->externalId = $demoRow[self::ATTR_EXTERNAL];
        $stockData->note = $demoRow[self::ATTR_NOTE];

        $isCentralStock = in_array($demoRow[self::ATTR_REFERENCE_NAME], self::CENTRAL_STOCK_REFERENCES, true);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $isEnabled = in_array($domainConfig->getId(), $enabledDomainIds, true);

            $stockData->isEnabledByDomain[$domainConfig->getId()] = $isEnabled;
            $stockData->isDefaultByDomain[$domainConfig->getId()] = $isEnabled && $isCentralStock;
        }

        return $stockData;
    }

    private function getDefaultDemoData(): array
    {
        return [
            [
                self::ATTR_NAME => 'Central warehouse CZ',
                self::ATTR_EXTERNAL => '800-cz',
                self::ATTR_NOTE => 'Update data in IS after goods are issued',
                self::ATTR_REFERENCE_NAME => self::STOCK_CENTRAL_CZ,
            ],
            [
                self::ATTR_NAME => 'Warehouse Ostrava',
                self::ATTR_EXTERNAL => '701-cz',
                self::ATTR_NOTE => 'Entry on the right',
                self::ATTR_REFERENCE_NAME => self::STOCK_OSTRAVA,
            ],
            [
                self::ATTR_NAME => 'Warehouse Pardubice',
                self::ATTR_EXTERNAL => '702-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_PARDUBICE,
            ],
            [
                self::ATTR_NAME => 'Warehouse Brno',
                self::ATTR_EXTERNAL => '703-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_BRNO,
            ],
            [
                self::ATTR_NAME => 'Warehouse Praha',
                self::ATTR_EXTERNAL => '704-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_PRAHA,
            ],
            [
                self::ATTR_NAME => 'Warehouse Hradec Králové',
                self::ATTR_EXTERNAL => '705-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_HRADEC_KRALOVE,
            ],
            [
                self::ATTR_NAME => 'Warehouse Olomouc',
                self::ATTR_EXTERNAL => '706-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_OLOMOUC,
            ],
            [
                self::ATTR_NAME => 'Warehouse Liberec',
                self::ATTR_EXTERNAL => '707-cz',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_LIBEREC,
            ],
        ];
    }

    private function getSkDemoData(): array
    {
        return [
            [
                self::ATTR_NAME => 'Central warehouse SK',
                self::ATTR_EXTERNAL => '801-sk',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_CENTRAL_SK,
            ],
            [
                self::ATTR_NAME => 'Warehouse Žilina',
                self::ATTR_EXTERNAL => '731-sk',
                self::ATTR_NOTE => 'Shortened opening hours',
                self::ATTR_REFERENCE_NAME => self::STOCK_ZILINA,
            ],
            [
                self::ATTR_NAME => 'Warehouse Bratislava',
                self::ATTR_EXTERNAL => '732-sk',
                self::ATTR_NOTE => 'Key is under the mat',
                self::ATTR_REFERENCE_NAME => self::STOCK_BRATISLAVA,
            ],
            [
                self::ATTR_NAME => 'Warehouse Banská Bystrica',
                self::ATTR_EXTERNAL => '733-sk',
                self::ATTR_NOTE => null,
                self::ATTR_REFERENCE_NAME => self::STOCK_BANSKA_BYSTRICA,
            ],
        ];
    }
}
