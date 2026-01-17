<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\Exception\GiftPlanNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

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
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan[]
     */
    public function findActiveGiftPlansByMainProductAndDomainId(Product $mainProduct, int $domainId): array
    {
        $now = $this->clock->now();

        $qb = $this->getRepository()->createQueryBuilder('gp')
            ->innerJoin('gp.mainProducts', 'mp')
            ->where('mp = :mainProduct')
            ->andWhere('gp.domainId = :domainId')
            ->andWhere('(gp.validFrom IS NULL OR gp.validFrom < :now)')
            ->andWhere('(gp.validTo IS NULL OR gp.validTo > :now)')
            ->setParameter('mainProduct', $mainProduct)
            ->setParameter('now', $now)
            ->setParameter('domainId', $domainId)
            ->orderBy('gp.id', 'ASC');

        return $qb->getQuery()->getResult();
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

        $now = $this->clock->now();

        $qb = $this->getRepository()->createQueryBuilder('gp')
            ->select('DISTINCT mp.id AS mainProductId, gift.id AS giftProductId')
            ->innerJoin('gp.mainProducts', 'mp')
            ->innerJoin('gp.giftProduct', 'gift')
            ->where('mp.id IN (:mainProductIds)')
            ->andWhere('gp.domainId = :domainId')
            ->andWhere('(gp.validFrom IS NULL OR gp.validFrom < :now)')
            ->andWhere('(gp.validTo IS NULL OR gp.validTo > :now)')
            ->setParameter('mainProductIds', $mainProductIds)
            ->setParameter('domainId', $domainId)
            ->setParameter('now', $now)
            ->orderBy('mp.id', 'ASC')
            ->addOrderBy('gift.id', 'ASC');

        $rows = $qb->getQuery()->getArrayResult();

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
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan[]
     */
    public function findAll(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan[]
     */
    public function findByGiftProductId(int $giftProductId): array
    {
        return $this->getRepository()->findBy(['giftProduct' => $giftProductId], ['id' => 'ASC']);
    }
}
