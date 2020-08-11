<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Stock\StockData;
use App\Model\Stock\StockDataFactory;
use App\Model\Stock\StockFacade;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StocksDataFixture extends AbstractReferenceFixture
{
    protected const ATTR_NAME = 'name';
    protected const ATTR_CENTRAL = 'centralStock';
    public const ATTR_EXTERNAL = 'externalId';
    protected const ATTR_CITY = 'city';
    protected const ATTR_STREET = 'street';
    protected const ATTR_OPENING_HOURS = 'openingHours';
    protected const ATTR_EXTRA_OPENING_HOURS = 'extraordinaryOpeningHours';
    protected const ATTR_LOCATION_LAT = 'locationLat';
    protected const ATTR_LOCATION_LNG = 'locationLng';
    protected const ATTR_CONTACT_INFO = 'contactInfo';

    public const STOCK_PREFIX = 'stock_';

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Stock\StockFacade
     */
    private $stockFacade;

    /**
     * @var \App\Model\Stock\StockDataFactory
     */
    private $stockDataFactory;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\StockDataFactory $stockDataFactory
     */
    public function __construct(Domain $domain, StockFacade $stockFacade, StockDataFactory $stockDataFactory)
    {
        $this->domain = $domain;
        $this->stockFacade = $stockFacade;
        $this->stockDataFactory = $stockDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($this->getDemoData($domainId) as $demoRow) {
                $stock = $this->stockFacade->create($this->initStockData($domainId, $demoRow));
                $this->addReferenceForDomain(self::STOCK_PREFIX . $stock->getExternalId(), $stock, $domainId);
            }
        }
    }

    /**
     * @param int $domainId
     * @param mixed $demoRow
     * @return \App\Model\Stock\StockData
     */
    protected function initStockData(int $domainId, $demoRow): StockData
    {
        $stockData = $this->stockDataFactory->create();
        $stockData->domainId = $domainId;
        $stockData->name = $demoRow[self::ATTR_NAME];
        $stockData->centralStock = $demoRow[self::ATTR_CENTRAL];
        $stockData->externalId = $demoRow[self::ATTR_EXTERNAL];
        $stockData->city = $demoRow[self::ATTR_CITY];
        $stockData->street = $demoRow[self::ATTR_STREET];
        $stockData->openingHours = $demoRow[self::ATTR_OPENING_HOURS];
        $stockData->extraordinaryOpeningHours = $demoRow[self::ATTR_EXTRA_OPENING_HOURS];
        $stockData->locationLat = $demoRow[self::ATTR_LOCATION_LAT];
        $stockData->locationLng = $demoRow[self::ATTR_LOCATION_LNG];
        $stockData->contactInfo = $demoRow[self::ATTR_CONTACT_INFO];

        return $stockData;
    }

    /**
     * @param int $domainId
     * @return array
     */
    public static function getDemoData(int $domainId): array
    {
        if ($domainId > 2) {
            $domainId = 1;
        }

        $data[1] = [
                [
                    self::ATTR_NAME => 'Praha - Stodůlky',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '701-cz',
                    self::ATTR_CITY => 'Praha 5-Stodůlky',
                    self::ATTR_STREET => 'Jeremiášova 947',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 50.055225,
                    self::ATTR_LOCATION_LNG => 14.310772,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Modřice u Brna',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '704-cz',
                    self::ATTR_CITY => '664 42 Modřice u Brna',
                    self::ATTR_STREET => 'Svratecká 879',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 49.133559,
                    self::ATTR_LOCATION_LNG => 16.63433,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Centrální sklad',
                    self::ATTR_CENTRAL => true,
                    self::ATTR_EXTERNAL => '800-cz',
                    self::ATTR_CITY => null,
                    self::ATTR_STREET => null,
                    self::ATTR_OPENING_HOURS => null,
                    self::ATTR_EXTRA_OPENING_HOURS => null,
                    self::ATTR_LOCATION_LAT => null,
                    self::ATTR_LOCATION_LNG => null,
                    self::ATTR_CONTACT_INFO => null,
                ],
                [
                    self::ATTR_NAME => 'Ostrava - Mariánské Hory',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '702-cz',
                    self::ATTR_CITY => '709 00 Ostrava-Mariánské Hory',
                    self::ATTR_STREET => 'Grmelova 2033/4',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 49.836419,
                    self::ATTR_LOCATION_LNG => 18.250427,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Praha - Černý most',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '703-cz',
                    self::ATTR_CITY => '198 29 Praha 9 - Černý Most',
                    self::ATTR_STREET => 'Chlumecká 2420',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 20:00 hod',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 50.111763,
                    self::ATTR_LOCATION_LNG => 14.583107,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Liberec',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '705-cz',
                    self::ATTR_CITY => '463 12 Liberec',
                    self::ATTR_STREET => 'České mládeže 570',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00 hod',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 50.741511,
                    self::ATTR_LOCATION_LNG => 15.044955,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Ústí nad Labem',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '706-cz',
                    self::ATTR_CITY => '400 04 Trmice',
                    self::ATTR_STREET => 'Tyršova ul. 887 (u OC Trmice)',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00 hod',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 50.649204,
                    self::ATTR_LOCATION_LNG => 14.006566,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'České Budějovice',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '707-cz',
                    self::ATTR_CITY => '370 04 České Budějovice',
                    self::ATTR_STREET => 'Strakonická 2800',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00 hod',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 48.994838,
                    self::ATTR_LOCATION_LNG => 14.463912,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
                [
                    self::ATTR_NAME => 'Hradec Králové',
                    self::ATTR_CENTRAL => false,
                    self::ATTR_EXTERNAL => '708-cz',
                    self::ATTR_CITY => 'Rovná 1724',
                    self::ATTR_STREET => '503 32 Hradec Králové',
                    self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00 So-Ne 09:00-20:00 hod',
                    self::ATTR_EXTRA_OPENING_HOURS => 'V tuto chvíli platí běžná otevírací doba',
                    self::ATTR_LOCATION_LAT => 50.183762,
                    self::ATTR_LOCATION_LNG => 15.801779,
                    self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
                ],
            ];
        $data[2] = [
            [
                self::ATTR_NAME => 'Centrální sklad SK',
                self::ATTR_CENTRAL => true,
                self::ATTR_EXTERNAL => '801-sk',
                self::ATTR_CITY => null,
                self::ATTR_STREET => null,
                self::ATTR_OPENING_HOURS => null,
                self::ATTR_EXTRA_OPENING_HOURS => null,
                self::ATTR_LOCATION_LAT => null,
                self::ATTR_LOCATION_LNG => null,
                self::ATTR_CONTACT_INFO => null,
            ],
            [
                self::ATTR_NAME => 'Trnava',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => '731-sk',
                self::ATTR_CITY => '917 01 Trnava',
                self::ATTR_STREET => 'Nová 8144/10',
                self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                self::ATTR_EXTRA_OPENING_HOURS => 'V tejto chvíli platí bežná otváracia doba',
                self::ATTR_LOCATION_LAT => 48.36452,
                self::ATTR_LOCATION_LNG => 17.6093399,
                self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
            ],
            [
                self::ATTR_NAME => 'Nitra',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => '732-sk',
                self::ATTR_CITY => '951 41 Lužianky pri Nitre',
                self::ATTR_STREET => 'Hlohovecká 2',
                self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                self::ATTR_EXTRA_OPENING_HOURS => 'V tejto chvíli platí bežná otváracia doba',
                self::ATTR_LOCATION_LAT => 48.3243566,
                self::ATTR_LOCATION_LNG => 18.0328114,
                self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
            ],
            [
                self::ATTR_NAME => 'Bratislava',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => '733-sk',
                self::ATTR_CITY => '821 02 Bratislava - Ružinov',
                self::ATTR_STREET => 'Obch. zóna PHAROS (pri letisku)',
                self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                self::ATTR_EXTRA_OPENING_HOURS => 'V tejto chvíli platí bežná otváracia doba',
                self::ATTR_LOCATION_LAT => 48.1633175,
                self::ATTR_LOCATION_LNG => 17.1847873,
                self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
            ],
            [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_CENTRAL => false,
                self::ATTR_EXTERNAL => '734-sk',
                self::ATTR_CITY => '010 01 Žilina',
                self::ATTR_STREET => 'Košická ulica 8962/22',
                self::ATTR_OPENING_HOURS => 'Po-Ne 09:00 - 19:00',
                self::ATTR_EXTRA_OPENING_HOURS => 'V tejto chvíli platí bežná otváracia doba',
                self::ATTR_LOCATION_LAT => 49.2086953,
                self::ATTR_LOCATION_LNG => 18.7816017,
                self::ATTR_CONTACT_INFO => 'Zaujala vás nabídka v našem e-shopu? Chtěli byste zboží před koupí vidět ve skutečné podobě? V tom případě vás rádi přivítáme v našem obchodním domě v Praze Stodůlkách. Nábytek si zde můžete nejen prohlédnout, ale i osahat a vyzkoušet. Věříme, že osobní prožitek je víc, než podrobný popis.',
            ],
        ];

        return $data[$domainId];
    }
}
