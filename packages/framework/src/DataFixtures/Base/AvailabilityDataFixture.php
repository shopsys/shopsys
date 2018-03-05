<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;

class AvailabilityDataFixture extends AbstractReferenceFixture
{
    const AVAILABILITY_IN_STOCK = 'availability_in_stock';
    const AVAILABILITY_ON_REQUEST = 'availability_on_request';
    const AVAILABILITY_OUT_OF_STOCK = 'availability_out_of_stock';
    const AVAILABILITY_PREPARING = 'availability_preparing';

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $availabilityData = new AvailabilityData();
        $availabilityData->name = ['cs' => 'Připravujeme', 'en' => 'Preparing'];
        $availabilityData->dispatchTime = 14;
        $this->createAvailability($availabilityData, self::AVAILABILITY_PREPARING);

        $availabilityData->name = ['cs' => 'Skladem', 'en' => 'In stock'];
        $availabilityData->dispatchTime = 0;
        $this->createAvailability($availabilityData, self::AVAILABILITY_IN_STOCK);

        $availabilityData->name = ['cs' => 'Na dotaz', 'en' => 'On request'];
        $availabilityData->dispatchTime = 7;
        $this->createAvailability($availabilityData, self::AVAILABILITY_ON_REQUEST);

        $availabilityData->name = ['cs' => 'Nedostupné', 'en' => 'Out of stock'];
        $availabilityData->dispatchTime = null;
        $this->createAvailability($availabilityData, self::AVAILABILITY_OUT_OF_STOCK);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @param string|null $referenceName
     */
    private function createAvailability(AvailabilityData $availabilityData, $referenceName = null)
    {
        $availabilityFacade = $this->get(AvailabilityFacade::class);
        /* @var $availabilityFacade \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade */

        $availability = $availabilityFacade->create($availabilityData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $availability);
        }
    }
}
