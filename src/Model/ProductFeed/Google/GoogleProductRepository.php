<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Google;

use App\Model\Stock\ProductStock;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomain;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductRepository as BaseGoogleProductRepository;

class GoogleProductRepository extends BaseGoogleProductRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \App\Model\Product\Product[]|\Doctrine\Common\Collections\Collection
     */
    public function getProducts(DomainConfig $domainConfig, PricingGroup $pricingGroup, ?int $lastSeekId, int $maxResults): iterable
    {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($domainConfig->getId(), $pricingGroup)
            ->addSelect('b')->leftJoin('p.brand', 'b')
            ->leftJoin(GoogleProductDomain::class, 'gpd', Join::WITH, 'gpd.product = p AND gpd.domainId = :domainId')
            ->andWhere('p.variantType != :variantTypeMain')->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->andWhere('gpd IS NULL OR gpd.show = TRUE')
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ProductStock::class, 'ps')
            ->join('ps.stock', 's', Join::WITH, 's.domainId = :domainId')
            ->where('ps.product = p')
            ->having('SUM(ps.productQuantity) > 0');
        $queryBuilder->andWhere('p.preorder = true OR EXISTS(' . $subquery->getDQL() . ')');

        $this->productRepository->addTranslation($queryBuilder, $domainConfig->getLocale());
        $this->productRepository->addDomain($queryBuilder, $domainConfig->getId());
        $queryBuilder->addSelect('v')->join('pd.vat', 'v');

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
