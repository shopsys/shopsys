<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;

class AvailabilityDataFixture extends AbstractReferenceFixture
{
    public const AVAILABILITY_IN_STOCK = 'availability_in_stock';
    public const AVAILABILITY_ON_REQUEST = 'availability_on_request';
    public const AVAILABILITY_OUT_OF_STOCK = 'availability_out_of_stock';
    public const AVAILABILITY_PREPARING = 'availability_preparing';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface
     */
    protected $availabilityDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface $availabilityDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        AvailabilityFacade $availabilityFacade,
        AvailabilityDataFactoryInterface $availabilityDataFactory,
        Setting $setting,
        Domain $domain
    ) {
        $this->availabilityFacade = $availabilityFacade;
        $this->availabilityDataFactory = $availabilityDataFactory;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $availabilityData = $this->availabilityDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('Preparing', [], 'dataFixtures', $locale);
        }

        $availabilityData->dispatchTime = 14;
        $this->createAvailability($availabilityData, self::AVAILABILITY_PREPARING);

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('In stock', [], 'dataFixtures', $locale);
        }

        $availabilityData->dispatchTime = 0;
        $inStockAvailability = $this->createAvailability($availabilityData, self::AVAILABILITY_IN_STOCK);
        $this->setting->set(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $inStockAvailability->getId());

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('On request', [], 'dataFixtures', $locale);
        }

        $availabilityData->dispatchTime = 7;
        $this->createAvailability($availabilityData, self::AVAILABILITY_ON_REQUEST);

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('Out of stock', [], 'dataFixtures', $locale);
        }

        $availabilityData->dispatchTime = null;
        $this->createAvailability($availabilityData, self::AVAILABILITY_OUT_OF_STOCK);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @param string|null $referenceName
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    protected function createAvailability(AvailabilityData $availabilityData, $referenceName = null)
    {
        $availability = $this->availabilityFacade->create($availabilityData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $availability);
        }

        return $availability;
    }
}
