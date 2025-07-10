<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Stock\Stock;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class StoreDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '6ffdbcc0-fd5d-4f60-bf8d-1349366b3d93';

    private const string ATTR_NAME = 'name';
    private const string ATTR_STOCK = 'stockId';
    private const string ATTR_IS_DEFAULT = 'isDefault';
    private const string ATTR_DOMAIN_ID = 'domainId';
    private const string ATTR_DESCRIPTION = 'description';
    private const string ATTR_EXTERNAL_ID = 'externalId';
    private const string ATTR_STREET = 'street';
    private const string ATTR_CITY = 'city';
    private const string ATTR_POSTCODE = 'postcode';
    private const string ATTR_COUNTRY = 'country';
    private const string ATTR_EMAIL = 'email';
    private const string ATTR_PHONE = 'phone';
    private const string ATTR_DIRECTIONS = 'directions';
    private const string ATTR_SPECIAL_MESSAGE = 'specialMessage';
    private const string ATTR_LOCATION_LATITUDE = 'latitude';
    private const string ATTR_LOCATION_LONGITUDE = 'longitude';
    private const string ATTR_IMAGE = 'image';
    public const string STORE_PREFIX = 'store_';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreDataFactory $storeDataFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHourDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory $openingHoursRangeDataFactory
     */
    public function __construct(
        private readonly StoreFacade $storeFacade,
        private readonly StoreDataFactory $storeDataFactory,
        private readonly ImageUploadDataFactory $imageUploadDataFactory,
        private readonly OpeningHoursDataFactory $openingHourDataFactory,
        private readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDemoData() as $demoRow) {
            $store = $this->storeFacade->create($this->initStoreData($demoRow));
            $this->addReference(self::STORE_PREFIX . $store->getId(), $store);
        }
    }

    /**
     * @return array
     */
    private function getDemoData(): array
    {
        $firstDomainConfig = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig();

        $stores = [
            [
                self::ATTR_NAME => 'Ostrava',
                self::ATTR_IS_DEFAULT => true,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 4, Stock::class),
                self::ATTR_DESCRIPTION => t('Store in Ostrava Přívoz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Koksární 10',
                self::ATTR_CITY => 'Ostrava',
                self::ATTR_POSTCODE => '70200',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'ostrava@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => t('If you take buses 9, 13 and 24 from the main station, get off at the Outlet Arena Moravia stop. There is no train stop, but there is a parking lot for horses.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_SPECIAL_MESSAGE => t('Tomorrow will be 20% discount for all items', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_LOCATION_LATITUDE => '49.8574975',
                self::ATTR_LOCATION_LONGITUDE => '18.2738861',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ], [
                self::ATTR_NAME => 'Pardubice',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => t('Store v Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Bratranců Veverkových 2722',
                self::ATTR_CITY => 'Pardubice',
                self::ATTR_POSTCODE => '53002',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'pardubice@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.0346875',
                self::ATTR_LOCATION_LONGITUDE => '15.7707169',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Brno',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Křenová 88',
                self::ATTR_CITY => 'Brno',
                self::ATTR_POSTCODE => '60200',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'brno@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.1950606',
                self::ATTR_LOCATION_LONGITUDE => '16.6084842',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Praha',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Vodičkova 791/41',
                self::ATTR_CITY => 'Praha',
                self::ATTR_POSTCODE => '11000',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'praha@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.0802931',
                self::ATTR_LOCATION_LONGITUDE => '14.4208994',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Hradec Králové',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Pražská 100',
                self::ATTR_CITY => 'Hradec Králové',
                self::ATTR_POSTCODE => '50002',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'hradec@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.2090192',
                self::ATTR_LOCATION_LONGITUDE => '15.8328583',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Olomouc',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Křížkovského 8',
                self::ATTR_CITY => 'Olomouc',
                self::ATTR_POSTCODE => '77900',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'olomouc@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.5951442',
                self::ATTR_LOCATION_LONGITUDE => '17.2500006',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Liberec',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Šaldova 1',
                self::ATTR_CITY => 'Liberec',
                self::ATTR_POSTCODE => '46001',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'liberec@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.7670131',
                self::ATTR_LOCATION_LONGITUDE => '15.0562825',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
            [
                self::ATTR_NAME => 'Plzeň',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $firstDomainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Klatovská 121',
                self::ATTR_CITY => 'Plzeň',
                self::ATTR_POSTCODE => '30100',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class),
                self::ATTR_EMAIL => 'plzen@shopsys.cz',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.7476961',
                self::ATTR_LOCATION_LONGITUDE => '13.3777325',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
        ];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if ($domainConfig === $firstDomainConfig) {
                continue;
            }

            $stores[] = [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $domainConfig->getId(),
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 14, Stock::class),
                self::ATTR_DESCRIPTION => t('Store in Žilina', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Pribinova 62',
                self::ATTR_CITY => 'Žilina',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                self::ATTR_POSTCODE => '01007',
                self::ATTR_EMAIL => 'zilina@shopsys.sk',
                self::ATTR_PHONE => '+421 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.2030444',
                self::ATTR_LOCATION_LONGITUDE => '18.7499042',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ];

            $stores[] = [
                self::ATTR_NAME => 'Bratislava',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $domainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Kováčska 4-6',
                self::ATTR_CITY => 'Bratislava',
                self::ATTR_POSTCODE => '83104',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                self::ATTR_EMAIL => 'bratislava@shopsys.sk',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '48.157054',
                self::ATTR_LOCATION_LONGITUDE => '17.124221',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ];

            $stores[] = [
                self::ATTR_NAME => 'Banská Bystrica',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $domainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Nad plážou 29',
                self::ATTR_CITY => 'Banská Bystrica',
                self::ATTR_POSTCODE => '97401',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                self::ATTR_EMAIL => 'banska.bystrica@shopsys.sk',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '48.736918',
                self::ATTR_LOCATION_LONGITUDE => '19.130424',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ];

            $stores[] = [
                self::ATTR_NAME => 'Košice',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => $domainConfig->getId(),
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => '',
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Žižkova 23-33',
                self::ATTR_CITY => 'Košice',
                self::ATTR_POSTCODE => '04001',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                self::ATTR_EMAIL => 'kosice@shopsys.sk',
                self::ATTR_PHONE => '+420 123 456 789',
                self::ATTR_DIRECTIONS => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '48.713752',
                self::ATTR_LOCATION_LONGITUDE => '21.249161',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ];

            break;
        }

        return $stores;
    }

    /**
     * @param array $demoRow
     * @return \Shopsys\FrameworkBundle\Model\Store\StoreData
     */
    private function initStoreData(array $demoRow): StoreData
    {
        $storeData = $this->storeDataFactory->createForDomain($demoRow[self::ATTR_DOMAIN_ID]);

        $storeData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $demoRow[self::ATTR_NAME])->toString();

        $storeData->name = $demoRow[self::ATTR_NAME];
        $storeData->isDefault = $demoRow[self::ATTR_IS_DEFAULT];
        $storeData->stock = $demoRow[self::ATTR_STOCK];
        $storeData->description = $demoRow[self::ATTR_DESCRIPTION];
        $storeData->externalId = $demoRow[self::ATTR_EXTERNAL_ID];
        $storeData->street = $demoRow[self::ATTR_STREET];
        $storeData->city = $demoRow[self::ATTR_CITY];
        $storeData->postcode = $demoRow[self::ATTR_POSTCODE];
        $storeData->country = $demoRow[self::ATTR_COUNTRY];
        $storeData->openingHours = $this->createOpeningHoursData();
        $storeData->email = $demoRow[self::ATTR_EMAIL];
        $storeData->phone = $demoRow[self::ATTR_PHONE];
        $storeData->directions = $demoRow[self::ATTR_DIRECTIONS];
        $storeData->specialMessage = $demoRow[self::ATTR_SPECIAL_MESSAGE];
        $storeData->latitude = $demoRow[self::ATTR_LOCATION_LATITUDE];
        $storeData->longitude = $demoRow[self::ATTR_LOCATION_LONGITUDE];
        $storeData->image = $demoRow[self::ATTR_IMAGE];

        return $storeData;
    }

    /**
     * @return array
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            StocksDataFixture::class,
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    private function createOpeningHoursData(): array
    {
        $openingHoursDataArray = [];
        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(1);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('06:00', '11:00'),
            $this->openingHoursRangeDataFactory->create('13:00', '18:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(2);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('07:00', '11:00'),
            $this->openingHoursRangeDataFactory->create('13:00', '17:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(3);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('08:00', '11:00'),
            $this->openingHoursRangeDataFactory->create('13:00', '16:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(4);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('09:00', '11:00'),
            $this->openingHoursRangeDataFactory->create('13:00', '15:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(5);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('10:00', '11:00'),
            $this->openingHoursRangeDataFactory->create('13:00', '14:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(6);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('08:00', '11:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        $openingHoursData = $this->openingHourDataFactory->createForDayOfWeek(7);
        $openingHoursData->openingHoursRanges = [
            $this->openingHoursRangeDataFactory->create('09:00', '11:00'),
        ];
        $openingHoursDataArray[] = $openingHoursData;

        return $openingHoursDataArray;
    }
}
