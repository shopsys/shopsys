<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison;

use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Comparison\Item\ComparedItem;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ComparisonRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Comparison::class);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function findByCustomerUser(CustomerUser $customerUser): ?Comparison
    {
        return $this->getRepository()->findOneBy(['customerUser' => $customerUser]);
    }

    /**
     * @param string|null $uuid
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function findByUuid(?string $uuid): ?Comparison
    {
        return $this->getRepository()->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Comparison\Comparison|null
     */
    public function findById(int $id): ?Comparison
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param \App\Model\Product\Comparison\Comparison $comparison
     * @return int[]
     */
    public function getProductIdsByComparison(Comparison $comparison): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(ComparedItem::class, 'ci')
            ->join('ci.product', 'p')
            ->where('ci.comparison = :comparison')
            ->orderBy('ci.createdAt', 'DESC')
            ->setParameter('comparison', $comparison)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'id');
    }

    /**
     * @param int $comparisonLifetimeDays
     */
    public function removeOldComparison(int $comparisonLifetimeDays): void
    {
        $removeDate = new DateTime();
        $removeDate->modify(sprintf('-%dday', $comparisonLifetimeDays));

        $this->em->createQueryBuilder()
            ->delete(Comparison::class, 'c')
            ->where('c.customerUser IS NULL')
            ->andWhere('c.updatedAt < :removeDate')
            ->setParameter('removeDate', $removeDate)
            ->getQuery()
            ->execute();
    }
}
