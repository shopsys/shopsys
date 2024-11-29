<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\PriceList\ProductWithPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;

class SpecialPriceRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param int[] $variantIds
     * @return array<int, array{priceAmount:\Shopsys\FrameworkBundle\Component\Money\Money, validFrom: \DateTimeImmutable, validTo: \DateTimeImmutable, productId: int}>
     */
    public function getCurrentAndFutureSpecialPrices(Product $product, int $domainId, array $variantIds = []): array
    {
        $queryBuilder = $this->getCurrentAndFutureSpecialPricesQueryBuilder($product, $domainId, $variantIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param int[] $variantIds
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getCurrentAndFutureSpecialPricesQueryBuilder(
        Product $product,
        int $domainId,
        array $variantIds = [],
    ): QueryBuilder {
        $currentDate = new DateTimeImmutable();

        return $this->em->createQueryBuilder()
            ->select('pwp.priceAmount, pl.validFrom, pl.validTo, IDENTITY(pwp.product) as productId')
            ->from(ProductWithPrice::class, 'pwp')
            ->join('pwp.priceList', 'pl')
            ->where('pwp.product IN (:productIds)')
            ->andWhere('pl.domainId = :domainId')
            ->andWhere('
            (:currentDate BETWEEN pl.validFrom AND pl.validTo)
            OR (:currentDate < pl.validFrom)
        ')
            ->setParameter('productIds', [...$variantIds, $product->getId()])
            ->setParameter('domainId', $domainId)
            ->setParameter('currentDate', $currentDate)
            ->orderBy('CASE
                WHEN :currentDate BETWEEN pl.validFrom AND pl.validTo THEN 1
                ELSE 2
            END', 'ASC') // Current price lists (1) are prioritized over future ones (2)
            ->addOrderBy('CASE
                WHEN :currentDate BETWEEN pl.validFrom AND pl.validTo THEN pl.lastUpdate ELSE :minDate
            END', 'DESC')
            ->addOrderBy('pl.validFrom', 'ASC') // Current sorted by lastUpdate DESC, future by validFrom ASC
            ->setParameter('minDate', new DateTimeImmutable('1970-01-01 00:00:00'));
    }
}
