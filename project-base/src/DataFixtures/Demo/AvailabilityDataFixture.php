<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityDataFactory $availabilityDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly AvailabilityFacade $availabilityFacade,
        private readonly AvailabilityDataFactoryInterface $availabilityDataFactory,
        private readonly Setting $setting,
        private readonly Domain $domain
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $availabilityData = $this->availabilityDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('Preparing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $availabilityData->dispatchTime = 14;
        $this->createAvailability($availabilityData, self::AVAILABILITY_PREPARING);

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('In stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $availabilityData->dispatchTime = 0;
        $inStockAvailability = $this->createAvailability($availabilityData, self::AVAILABILITY_IN_STOCK);
        $this->setting->set(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $inStockAvailability->getId());

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('On request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $availabilityData->dispatchTime = 7;
        $this->createAvailability($availabilityData, self::AVAILABILITY_ON_REQUEST);

        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = t('Out of stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $availabilityData->dispatchTime = null;
        $this->createAvailability($availabilityData, self::AVAILABILITY_OUT_OF_STOCK);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @param string|null $referenceName
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    private function createAvailability(AvailabilityData $availabilityData, $referenceName = null)
    {
        $availability = $this->availabilityFacade->create($availabilityData);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $availability);
        }

        return $availability;
    }
}
