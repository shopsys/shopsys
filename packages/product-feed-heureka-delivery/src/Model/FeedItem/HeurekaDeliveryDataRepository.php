<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Stock\ProductStock;

class HeurekaDeliveryDataRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return array[]
     */
    public function getDataRows(
        DomainConfig $domainConfig,
        PricingGroup $pricingGroup,
        ?int $lastSeekId,
        int $maxResults,
    ): array {
        $queryBuilder = $this->productRepository->getAllSellableQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder
            ->andWhere('p.productType != :inquiryProductType')
            ->setParameter('inquiryProductType', ProductTypeEnum::TYPE_INQUIRY);
        $queryBuilder->leftJoin(ProductStock::class, 'ps', Join::WITH, 'ps.product = p');
        $queryBuilder->having('SUM(ps.productQuantity) > 0');


        $queryBuilder->select('p.id, SUM(ps.productQuantity) as stockQuantity')
            ->groupBy('p.id')
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->getScalarResult();
    }
}
