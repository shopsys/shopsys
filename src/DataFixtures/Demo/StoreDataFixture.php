<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Store\StoreData;
use App\Model\Store\StoreDataFactory;
use App\Model\Store\StoreFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

class StoreDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const ATTR_NAME = 'name';
    private const ATTR_STOCK = 'stockId';
    private const ATTR_IS_DEFAULT = 'isDefault';
    private const ATTR_IS_ENABLED_BY_DOMAIN = 'isEnabledByDomain';
    private const ATTR_DESCRIPTION = 'description';
    private const ATTR_EXTERNAL_ID = 'externalId';
    private const ATTR_ADDRESS = 'address';
    private const ATTR_OPENING_HOURS = 'openingHours';
    private const ATTR_CONTACT_INFO = 'contactInfo';
    private const ATTR_SPECIAL_MESSAGE = 'specialMessage';
    private const ATTR_LOCATION_LATITUDE = 'locationLatitude';
    private const ATTR_LOCATION_LONGITUDE = 'locationLongitude';
    private const ATTR_IMAGE = 'image';

    private const STORE_PREFIX = 'store_';

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \App\Model\Store\StoreDataFactory
     */
    private StoreDataFactory $storeDataFactory;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \App\Model\Store\StoreDataFactory $storeDataFactory
     */
    public function __construct(
        StoreFacade $storeFacade,
        StoreDataFactory $storeDataFactory
    ) {
        $this->storeFacade = $storeFacade;
        $this->storeDataFactory = $storeDataFactory;
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
        return [
            [
                self::ATTR_NAME => 'Ostrava',
                self::ATTR_IS_DEFAULT => true,
                self::ATTR_IS_ENABLED_BY_DOMAIN => [1 => true, 2 => false],
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 4),
                self::ATTR_DESCRIPTION => null,
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_ADDRESS => "Koksární 10\n702 00 Ostrava",
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_OPENING_HOURS => 'Po-Pa: 8:00-16:00',
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.8574975',
                self::ATTR_LOCATION_LONGITUDE => '18.2738861',
                self::ATTR_IMAGE => new ImageUploadData(),
            ], [
                self::ATTR_NAME => 'Pardubice',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_IS_ENABLED_BY_DOMAIN => [1 => true, 2 => false],
                self::ATTR_STOCK => null,
                self::ATTR_DESCRIPTION => null,
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_ADDRESS => "Bratranců Veverkových 2722\n530 02 Pardubice",
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_OPENING_HOURS => 'Po-Pa: 8:00-17:00',
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '50.0346875',
                self::ATTR_LOCATION_LONGITUDE => '15.7707169',
                self::ATTR_IMAGE => new ImageUploadData(),
            ], [
                self::ATTR_NAME => 'Žilina',
                self::ATTR_IS_DEFAULT => false,
                self::ATTR_IS_ENABLED_BY_DOMAIN => [1 => false, 2 => true],
                self::ATTR_STOCK => $this->getReference(StocksDataFixture::STOCK_PREFIX . 14),
                self::ATTR_DESCRIPTION => null,
                self::ATTR_EXTERNAL_ID => null,
                self::ATTR_ADDRESS => "Pribinova 62\n010 07 Žilina",
                self::ATTR_CONTACT_INFO => null,
                self::ATTR_OPENING_HOURS => 'Po-Pa: 7:00-16:00',
                self::ATTR_SPECIAL_MESSAGE => null,
                self::ATTR_LOCATION_LATITUDE => '49.2030444',
                self::ATTR_LOCATION_LONGITUDE => '18.7499042',
                self::ATTR_IMAGE => new ImageUploadData(),
            ],
        ];
    }

    /**
     * @param array $demoRow
     * @return \App\Model\Store\StoreData
     */
    private function initStoreData(array $demoRow): StoreData
    {
        $storeData = $this->storeDataFactory->create();

        $storeData->name = $demoRow[self::ATTR_NAME];
        $storeData->isDefault = $demoRow[self::ATTR_IS_DEFAULT];
        $storeData->isEnabledOnDomains = $demoRow[self::ATTR_IS_ENABLED_BY_DOMAIN];
        $storeData->stock = $demoRow[self::ATTR_STOCK];
        $storeData->description = $demoRow[self::ATTR_DESCRIPTION];
        $storeData->externalId = $demoRow[self::ATTR_EXTERNAL_ID];
        $storeData->address = $demoRow[self::ATTR_ADDRESS];
        $storeData->openingHours = $demoRow[self::ATTR_OPENING_HOURS];
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
}
