<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Heureka\FeedItem;

use App\Model\Stock\ProductStock;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem\HeurekaDeliveryDataRepository as BaseHeurekaDeliveryDataRepository;

class HeurekaDeliveryDataRepository extends BaseHeurekaDeliveryDataRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return array[]
     */
    public function getDataRows(DomainConfig $domainConfig, PricingGroup $pricingGroup, ?int $lastSeekId, int $maxResults): array
    {
        $queryBuilder = $this->productRepository->getAllSellableQueryBuilder($domainConfig->getId(), $pricingGroup);
        $queryBuilder->select('p.id, SUM(ps.productQuantity) as stockQuantity')
            ->join(ProductStock::class, 'ps', Join::WITH, 'ps.product = p')
            ->join('ps.stock', 's', Join::WITH, 's.domainId = :domainId')
            ->groupBy('p.id')
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->getScalarResult();
    }
}
