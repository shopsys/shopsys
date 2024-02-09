<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;

class StoreDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const ATTR_NAME = 'name';
    private const ATTR_STOCK = 'stockId';
    private const ATTR_IS_DEFAULT = 'isDefault';
    private const ATTR_DOMAIN_ID = 'domainId';
    private const ATTR_DESCRIPTION = 'description';
    private const ATTR_EXTERNAL_ID = 'externalId';
    private const ATTR_STREET = 'street';
    private const ATTR_CITY = 'city';
    private const ATTR_POSTCODE = 'postcode';
    private const ATTR_COUNTRY = 'country';
    private const ATTR_CONTACT_INFO = 'contactInfo';
    private const ATTR_SPECIAL_MESSAGE = 'specialMessage';
    private const ATTR_LOCATION_LATITUDE = 'locationLatitude';
    private const ATTR_LOCATION_LONGITUDE = 'locationLongitude';
    private const ATTR_IMAGE = 'image';
    public const STORE_PREFIX = 'store_';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '6ffdbcc0-fd5d-4f60-bf8d-1349366b3d93',
        '60a0cd42-5c7b-47a8-ac79-aec5808931ff',
        '9be1392b-c39a-4130-a107-aedc56e7175e',
    ];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreDataFactory $storeDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHourDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory $openingHoursRangeDataFactory
     */
    public function __construct(
        private readonly StoreFacade $storeFacade,
        private readonly StoreDataFactory $storeDataFactory,
        private readonly Domain $domain,
        private readonly ImageUploadDataFactory $imageUploadDataFactory,
        private readonly OpeningHoursDataFactory $openingHourDataFactory,
        private readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
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
        $firstDomainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        try {
            $secondDomainConfig = $this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID);
            $isSecondDomainAvailable = true;
        } catch (InvalidDomainIdException) {
            $isSecondDomainAvailable = false;
        }

        $stores = [
            [
                self::ATTR_NAME => 'Ostrava',
                self::ATTR_IS_DEFAULT => true,
                self::ATTR_DOMAIN_ID => Domain::FIRST_DOMAIN_ID,
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 4),
                self::ATTR_DESCRIPTION => t('Store in Ostrava Přívoz', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Koksární 10',
                self::ATTR_CITY => 'Ostrava',
                self::ATTR_POSTCODE => '70200',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.8574975',
                self::ATTR_LOCATION_LONGITUDE => '18.2738861',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ], [
                self::ATTR_NAME => 'Pardubice',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => Domain::FIRST_DOMAIN_ID,
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => t('Store v Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Bratranců Veverkových 2722',
                self::ATTR_CITY => 'Pardubice',
                self::ATTR_POSTCODE => '53002',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.0346875',
                self::ATTR_LOCATION_LONGITUDE => '15.7707169',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ],
        ];

        if ($isSecondDomainAvailable) {
            $stores[] = [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_DOMAIN_ID => Domain::SECOND_DOMAIN_ID,
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 14),
                self::ATTR_DESCRIPTION => t('Store in Žilina', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $secondDomainConfig->getLocale()),
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_STREET => 'Pribinova 62',
                self::ATTR_CITY => 'Žilina',
                self::ATTR_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA),
                self::ATTR_POSTCODE => '01007',
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.2030444',
                self::ATTR_LOCATION_LONGITUDE => '18.7499042',
                self::ATTR_IMAGE => $this->imageUploadDataFactory->create(),
            ];
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

        $storeData->uuid = array_pop($this->uuidPool);

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
        $storeData->contactInfo = $demoRow[self::ATTR_CONTACT_INFO];
        $storeData->specialMessage = $demoRow[self::ATTR_SPECIAL_MESSAGE];
        $storeData->locationLatitude = $demoRow[self::ATTR_LOCATION_LATITUDE];
        $storeData->locationLongitude = $demoRow[self::ATTR_LOCATION_LONGITUDE];
        $storeData->image = $demoRow[self::ATTR_IMAGE];

        return $storeData;
    }

    public function getDependencies()
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
