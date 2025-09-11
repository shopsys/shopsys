<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\Exception\GiftPlanNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class GiftPlanRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(GiftPlan::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan|null
     */
    public function findById(int $id): ?GiftPlan
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan
     */
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
     * @param int $domainId
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan[]
     */
    public function findActiveGiftPlansByMainProductAndDomainId(Product $mainProduct, int $domainId): array
    {
        $now = new DateTime();

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
}
