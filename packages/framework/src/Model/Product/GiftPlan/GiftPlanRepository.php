<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\Exception\GiftPlanNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use SortDirection;

class GiftPlanRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(GiftPlan::class);
    }

    public function findById(int $id): ?GiftPlan
    {
        return $this->getRepository()->find($id);
    }

    public function getById(int $id): GiftPlan
    {
        $giftPlan = $this->findById($id);

        if ($giftPlan === null) {
            throw new GiftPlanNotFoundException('Gift plan with ID ' . $id . ' does not exist.');
        }

        return $giftPlan;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $mainProducts
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]>
     */
    public function findActiveGiftProductsByMainProducts(array $mainProducts, int $domainId): array
    {
        if ($mainProducts === []) {
            return [];
        }

        $mainProductIds = array_map(static fn (Product $product): int => $product->getId(), $mainProducts);
        $giftIdsByMainId = $this->findActiveGiftProductIdsByMainProductIds($mainProductIds, $domainId);

        $allGiftIds = $giftIdsByMainId
            ? array_values(array_unique(array_merge(...$giftIdsByMainId)))
            : [];

        $result = array_fill_keys(array_map('intval', $mainProductIds), []);

        if ($allGiftIds === []) {
            return $result;
        }

        $giftProducts = $this->productRepository->getAllByIds($allGiftIds);

        $giftProductsById = [];

        foreach ($giftProducts as $giftProduct) {
            $giftProductsById[$giftProduct->getId()] = $giftProduct;
        }

        foreach ($giftIdsByMainId as $mainId => $giftIds) {
            foreach ($giftIds as $giftId) {
                if (array_key_exists($giftId, $giftProductsById)) {
                    $result[$mainId][] = $giftProductsById[$giftId];
                }
            }
        }

        return $result;
    }

    /**
     * @param int[] $mainProductIds
     * @return int[][]
     */
    public function findActiveGiftProductIdsByMainProductIds(array $mainProductIds, int $domainId): array
    {
        if ($mainProductIds === []) {
            return [];
        }

        $rows = $this->createActiveGiftProductIdsQueryBuilder($mainProductIds)
            ->andWhere('gp.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getArrayResult();

        $giftIdsIndexedByMainProductId = array_fill_keys(array_map('intval', $mainProductIds), []);

        foreach ($rows as $row) {
            $mainId = (int)$row['mainProductId'];
            $giftId = (int)$row['giftProductId'];
            $giftIdsIndexedByMainProductId[$mainId][] = $giftId;
        }

        foreach ($giftIdsIndexedByMainProductId as $mainId => $giftIds) {
            $giftIdsIndexedByMainProductId[$mainId] = array_values(array_unique($giftIds));
        }

        return $giftIdsIndexedByMainProductId;
    }

    /**
     * @param int[] $mainProductIds
     * @return array<int, array<int, int[]>>
     */
    public function getActiveGiftProductIdsIndexedByDomainIdAndMainProductId(array $mainProductIds): array
    {
        if ($mainProductIds === []) {
            return [];
        }

        $rows = $this->createActiveGiftProductIdsQueryBuilder($mainProductIds)
            ->addSelect('gp.domainId AS domainId')
            ->getQuery()
            ->getArrayResult();

        $giftProductIdsIndexedByDomainIdAndMainProductId = [];

        foreach ($rows as $row) {
            $giftProductIdsIndexedByDomainIdAndMainProductId[(int)$row['domainId']][(int)$row['mainProductId']][] = (int)$row['giftProductId'];
        }

        return $giftProductIdsIndexedByDomainIdAndMainProductId;
    }

    /**
     * @param int[] $mainProductIds
     */
    protected function createActiveGiftProductIdsQueryBuilder(array $mainProductIds): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('gp')
            ->select('DISTINCT mp.id AS mainProductId, gift.id AS giftProductId')
            ->innerJoin('gp.mainProducts', 'mp')
            ->innerJoin('gp.giftProduct', 'gift')
            ->where('mp.id IN (:mainProductIds)')
            ->andWhere('(gp.validFrom IS NULL OR gp.validFrom < :now)')
            ->andWhere('(gp.validTo IS NULL OR gp.validTo > :now)')
            ->setParameter('mainProductIds', $mainProductIds)
            ->setParameter('now', $this->clock->now())
            ->orderBy('mp.id', SortDirection::Ascending)
            ->addOrderBy('gift.id', SortDirection::Ascending);
    }

    /**
     * @param int[] $giftProductIds
     * @return int[]
     */
    public function getMainProductIdsByGiftProductIds(array $giftProductIds): array
    {
        if ($giftProductIds === []) {
            return [];
        }

        $rows = $this->createMainProductIdsQueryBuilder()
            ->where('IDENTITY(gp.giftProduct) IN (:giftProductIds)')
            ->setParameter('giftProductIds', $giftProductIds)
            ->getQuery()
            ->getSingleColumnResult();

        return array_map('intval', $rows);
    }

    /**
     * @return int[]
     */
    public function getAllMainProductIds(): array
    {
        $rows = $this->createMainProductIdsQueryBuilder()
            ->getQuery()
            ->getSingleColumnResult();

        return array_map('intval', $rows);
    }

    protected function createMainProductIdsQueryBuilder(): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('gp')
            ->select('DISTINCT mp.id')
            ->innerJoin('gp.mainProducts', 'mp');
    }
}
