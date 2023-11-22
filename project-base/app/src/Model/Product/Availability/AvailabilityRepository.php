<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityRepository as BaseAvailabilityRepository;

class AvailabilityRepository extends BaseAvailabilityRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $oldAvailability
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $newAvailability
     */
    public function replaceAvailability(Availability $oldAvailability, Availability $newAvailability): void
    {
        $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.availability', ':newAvailability')->setParameter('newAvailability', $newAvailability)
            ->where('p.availability = :oldAvailability')->setParameter('oldAvailability', $oldAvailability)
            ->getQuery()->execute();
    }
}
