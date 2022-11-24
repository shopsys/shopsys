<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;

class AdvertRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdvertRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Advert::class);
    }

    /**
     * @param string $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findById(string $advertId): ?\Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        return $this->getAdvertRepository()->find($advertId);
    }

    /**
     * @param string $positionName
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAdvertByPositionQueryBuilder(string $positionName, int $domainId): \Doctrine\ORM\QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.positionName = :positionName')->setParameter('positionName', $positionName)
            ->andWhere('a.hidden = FALSE')
            ->andWhere('a.domainId = :domainId')->setParameter('domainId', $domainId);
    }

    /**
     * @param string $positionName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPosition(string $positionName, int $domainId): ?\Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        $count = $this->getAdvertByPositionQueryBuilder($positionName, $domainId)
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult();

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        if ((int)$count === 0) {
            return null;
        }

        return $this->getAdvertByPositionQueryBuilder($positionName, $domainId)
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()->getSingleResult();
    }

    /**
     * @param int $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function getById(int $advertId): \Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        $advert = $this->getAdvertRepository()->find($advertId);
        if ($advert === null) {
            $message = 'Advert with ID ' . $advertId . ' not found';
            throw new AdvertNotFoundException($message);
        }
        return $advert;
    }
}
