<?php

declare(strict_types=1);

namespace App\Model\Product\BestsellingProduct;

use App\Model\Product\ProductDomain;
use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\AutomaticBestsellingProductRepository as BaseAutomaticBestsellingProductRepository;

class AutomaticBestsellingProductRepository extends BaseAutomaticBestsellingProductRepository
{
    /**
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \DateTime $ordersCreatedAtLimit
     * @param int $maxResults
     * @return \App\Model\Product\Product[]
     */
    public function getOfferedProductsByCategory(
        $domainId,
        Category $category,
        PricingGroup $pricingGroup,
        DateTime $ordersCreatedAtLimit,
        $maxResults
    ) {
        $queryBuilder = $this->productRepository->getOfferedInCategoryQueryBuilder($domainId, $pricingGroup, $category);

        $queryBuilder
            ->addSelect('COUNT(op) AS HIDDEN orderCount')
            ->join(ProductDomain::class, 'pd', Join::WITH, 'pd.product = p')
            ->join(OrderItem::class, 'op', Join::WITH, 'op.product = p')
            ->join('op.order', 'o')
            ->join('o.status', 'os')
            ->andWhere('os.type = :orderStatusType')
            ->setParameter('orderStatusType', OrderStatus::TYPE_DONE)
            ->andWhere('o.createdAt >= :createdAt')
            ->setParameter('createdAt', $ordersCreatedAtLimit)
            ->orderBy('orderCount', 'DESC')
            ->addOrderBy('pd.lowPriceWithVat', 'DESC')
            ->groupBy('p.id, pd.product, pd.domainId')
            ->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->execute();
    }
}
