<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class AvailabilityDataFactory implements AvailabilityDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData
     */
    protected function createInstance(): AvailabilityData
    {
        return new AvailabilityData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData
     */
    public function create(): AvailabilityData
    {
        $availabilityData = $this->createInstance();
        $this->fillNew($availabilityData);

        return $availabilityData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     */
    protected function fillNew(AvailabilityData $availabilityData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $availabilityData->name[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData
     */
    public function createFromAvailability(Availability $availability): AvailabilityData
    {
        $availabilityData = $this->createInstance();
        $this->fillFromAvailability($availabilityData, $availability);

        return $availabilityData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     */
    protected function fillFromAvailability(AvailabilityData $availabilityData, Availability $availability): void
    {
        $availabilityData->dispatchTime = $availability->getDispatchTime();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation[] $translations */
        $translations = $availability->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $availabilityData->name = $names;
    }
}
