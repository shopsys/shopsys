<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class AvailabilityDataFactory implements AvailabilityDataFactoryInterface
{
    public function create(): AvailabilityData
    {
        return new AvailabilityData();
    }

    public function createFromAvailability(Availability $availability): AvailabilityData
    {
        $availabilityData = new AvailabilityData();
        $this->fillFromAvailability($availabilityData, $availability);

        return $availabilityData;
    }

    protected function fillFromAvailability(AvailabilityData $availabilityData, Availability $availability)
    {
        $availabilityData->dispatchTime = $availability->getDispatchTime();
        $translations = $availability->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $availabilityData->name = $names;
    }
}
