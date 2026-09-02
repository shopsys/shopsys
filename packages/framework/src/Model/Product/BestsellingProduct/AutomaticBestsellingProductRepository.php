<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class AutomaticBestsellingProductRepository
{
    public function __construct(protected readonly ProductRepository $productRepository)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProductsByCategory(
        int $domainId,
        Category $category,
        PricingGroup $pricingGroup,
        DateTimeImmutable $ordersCreatedAtLimit,
        int $maxResults,
    ): array {
        $queryBuilder = $this->productRepository->getOfferedInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        $queryBuilder
            ->addSelect('COUNT(op) AS HIDDEN orderCount')
            ->join(ProductManualInputPrice::class, 'pmip', Join::WITH, 'pmip.product = p')
            ->join(OrderItem::class, 'op', Join::WITH, 'op.product = p')
            ->join('op.order', 'o')
            ->join('o.status', 'os')
            ->andWhere('pmip.pricingGroup = prv.pricingGroup')
            ->andWhere('os.type = :orderStatusType')
            ->setParameter('orderStatusType', OrderStatusTypeEnum::TYPE_DONE)
            ->andWhere('o.domainId = :domainId')
            ->andWhere('o.createdAt >= :createdAt')
            ->setParameter('createdAt', $ordersCreatedAtLimit)
            ->orderBy('orderCount', 'DESC')
            ->addOrderBy('pmip.inputPrice', 'DESC')
            ->groupBy('p.id, pmip.product, pmip.pricingGroup')
            ->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->getResult();
    }
}
