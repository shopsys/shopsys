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

    /**
     * @var \App\Model\Product\Availability\AvailabilityFacade
     */
    protected $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface
     */
    protected $availabilityDataFactory;

    /**
     * @var \App\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \App\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactoryInterface $availabilityDataFactory
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Component\Domain\Domain $domain
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
            $availabilityData->name[$locale] = t('In stock', [], 'dataFixtures', $locale);
        }

        $availabilityData->dispatchTime = 0;
        $inStockAvailability = $this->createAvailability($availabilityData);
        $this->setting->set(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $inStockAvailability->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    protected function createAvailability(AvailabilityData $availabilityData)
    {
        $availability = $this->availabilityFacade->create($availabilityData);
        $this->addReference(self::AVAILABILITY_IN_STOCK, $availability);

        return $availability;
    }
}
