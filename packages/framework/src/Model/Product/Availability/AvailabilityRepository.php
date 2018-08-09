<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Model\Product\Product;

class AvailabilityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAvailabilityRepository()
    {
        return $this->em->getRepository(Availability::class);
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null
     */
    public function findById($availabilityId)
    {
        return $this->getAvailabilityRepository()->find($availabilityId);
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getById($availabilityId)
    {
        $availability = $this->findById($availabilityId);

        if ($availability === null) {
            $message = 'Availability with ID ' . $availabilityId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Product\Availability\Exception\AvailabilityNotFoundException($message);
        }

        return $availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAll()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('a')
            ->from(Availability::class, 'a')
            ->orderBy('a.dispatchTime');
        $query = $queryBuilder->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);

        return $query->execute();
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAllExceptId($availabilityId)
    {
        $qb = $this->getAvailabilityRepository()->createQueryBuilder('a')
            ->where('a.id != :id')
            ->setParameter('id', $availabilityId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return bool
     */
    public function isAvailabilityUsed(Availability $availability)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('p.id')
            ->from(Product::class, 'p')
            ->setMaxResults(1)
            ->where('p.availability = :availability OR p.outOfStockAvailability = :availability')
            ->setParameter('availability', $availability->getId());

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR) !== null;
    }

    public function replaceAvailability(Availability $oldAvailability, Availability $newAvailability)
    {
        $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.availability', ':newAvailability')->setParameter('newAvailability', $newAvailability)
            ->where('p.availability = :oldAvailability')->setParameter('oldAvailability', $oldAvailability)
            ->getQuery()->execute();

        $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.outOfStockAvailability', ':newAvailability')->setParameter('newAvailability', $newAvailability)
            ->where('p.outOfStockAvailability = :oldAvailability')->setParameter('oldAvailability', $oldAvailability)
            ->getQuery()->execute();

        $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedAvailability', ':newAvailability')->setParameter('newAvailability', $newAvailability)
            ->where('p.calculatedAvailability = :oldAvailability')->setParameter('oldAvailability', $oldAvailability)
            ->getQuery()->execute();
    }
}
