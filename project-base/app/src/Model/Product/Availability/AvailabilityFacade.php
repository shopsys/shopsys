<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade as BaseAvailabilityFacade;

/**
 * @property \App\Model\Product\Availability\AvailabilityRepository $availabilityRepository
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Availability\AvailabilityRepository $availabilityRepository, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler, \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFactoryInterface $availabilityFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
 */
class AvailabilityFacade extends BaseAvailabilityFacade
{
    /**
     * @param int $availabilityId
     * @param int|null $newAvailabilityId
     */
    public function deleteById($availabilityId, $newAvailabilityId = null): void
    {
        $availability = $this->availabilityRepository->getById($availabilityId);

        if ($newAvailabilityId !== null) {
            $newAvailability = $this->availabilityRepository->getById($newAvailabilityId);

            $this->availabilityRepository->replaceAvailability($availability, $newAvailability);

            if ($this->isAvailabilityDefault($availability)) {
                $this->setDefaultInStockAvailability($newAvailability);
            }
        }

        $this->em->remove($availability);

        $this->em->flush();
    }
}
