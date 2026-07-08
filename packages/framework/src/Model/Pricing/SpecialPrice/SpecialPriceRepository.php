<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPrice;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Clock\DatePoint;

class SpecialPriceRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return array{priceAmount:\Shopsys\FrameworkBundle\Component\Money\Money, validFrom: \DateTimeImmutable, validTo: \DateTimeImmutable, productListName: string, productListId: int, productId: int}|null
     */
    public function findRelevantSpecialPrice(Product $product, int $domainId, Currency $currency): ?array
    {
        $queryBuilder = $this->getCurrentAndFutureSpecialPricesQueryBuilder($product, $domainId, $currency)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int[] $variantIds
     * @return array<int, array{priceAmount:\Shopsys\FrameworkBundle\Component\Money\Money, validFrom: \DateTimeImmutable, validTo: \DateTimeImmutable, productListName: string, productListId: int, productId: int}>
     */
    public function getCurrentAndFutureSpecialPrices(
        Product $product,
        int $domainId,
        Currency $currency,
        array $variantIds = [],
    ): array {
        $queryBuilder = $this->getCurrentAndFutureSpecialPricesQueryBuilder($product, $domainId, $currency, $variantIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int[] $variantIds
     */
    protected function getCurrentAndFutureSpecialPricesQueryBuilder(
        Product $product,
        int $domainId,
        Currency $currency,
        array $variantIds = [],
    ): QueryBuilder {
        $currentDate = $this->clock->now();

        return $this->em->createQueryBuilder()
            ->select('pwp.priceAmount, pl.validFrom, pl.validTo, IDENTITY(pwp.product) as productId, pl.name as productListName, pl.id as productListId')
            ->from(PriceListProductPrice::class, 'pwp')
            ->join('pwp.priceList', 'pl')
            ->where('pwp.product IN (:productIds)')
            ->andWhere('pl.domainId = :domainId')
            ->andWhere('pwp.currency = :currency')
            ->andWhere('
            (:currentDate BETWEEN pl.validFrom AND pl.validTo)
            OR (:currentDate < pl.validFrom)
        ')
            ->setParameter('productIds', [...$variantIds, $product->getId()])
            ->setParameter('domainId', $domainId)
            ->setParameter('currency', $currency)
            ->setParameter('currentDate', $currentDate)
            ->orderBy('CASE
                WHEN :currentDate BETWEEN pl.validFrom AND pl.validTo THEN 1
                ELSE 2
            END', 'ASC') // Current price lists (1) are prioritized over future ones (2)
            ->addOrderBy('CASE
                WHEN :currentDate BETWEEN pl.validFrom AND pl.validTo THEN pl.lastUpdate ELSE :minDate
            END', 'DESC')
            ->addOrderBy('pl.validFrom', 'ASC') // Current sorted by lastUpdate DESC, future by validFrom ASC
            ->setParameter('minDate', new DatePoint('1970-01-01 00:00:00'));
    }
}
