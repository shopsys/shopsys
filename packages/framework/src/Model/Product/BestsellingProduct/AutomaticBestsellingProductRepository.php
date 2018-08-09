<?php

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use DateTime;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class AutomaticBestsellingProductRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param int $domainId
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
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
            ->join(ProductCalculatedPrice::class, 'pcp', Join::WITH, 'pcp.product = p')
            ->join(OrderProduct::class, 'op', Join::WITH, 'op.product = p')
            ->join('op.order', 'o')
            ->join('o.status', 'os')
            ->andWhere('pcp.pricingGroup = prv.pricingGroup')
            ->andWhere('os.type = :orderStatusType')
            ->setParameter('orderStatusType', OrderStatus::TYPE_DONE)
            ->andWhere('o.createdAt >= :createdAt')
            ->setParameter('createdAt', $ordersCreatedAtLimit)
            ->orderBy('orderCount', 'DESC')
            ->addOrderBy('pcp.priceWithVat', 'DESC')
            ->groupBy('p.id, pcp.product, pcp.pricingGroup')
            ->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->execute();
    }
}
