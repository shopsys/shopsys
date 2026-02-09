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
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
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
            ->select('p.id, SUM(ps.productQuantity) as stockQuantity')
            ->andWhere('p.productType != :inquiryProductType')
            ->setParameter('inquiryProductType', ProductTypeEnum::TYPE_INQUIRY)
            ->leftJoin(ProductStock::class, 'ps', Join::WITH, 'ps.product = p')
            ->join('ps.stock', 's')
            ->join('s.domains', 'sd', Join::WITH, 's.id = sd.stock AND sd.domainId = :domainId AND sd.isEnabled = TRUE')
            ->having('SUM(ps.productQuantity) > 0')
            ->groupBy('p.id')
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->getScalarResult();
    }
}
