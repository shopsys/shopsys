<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Zbozi;

use App\Model\Product\ProductRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductRepository as BaseZboziProductRepository;

class ZboziProductRepository extends BaseZboziProductRepository
{
    /**
     * @var \App\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

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
            ->leftJoin(ZboziProductDomain::class, 'zpd', Join::WITH, 'zpd.product = p AND zpd.domainId = :domainId')
            ->andWhere('p.variantType != :variantTypeMain')->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere('zpd IS NULL OR zpd.show = TRUE')
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        $this->productRepository->filterTemporaryExcludedProducts($queryBuilder, $domainConfig->getId());
        $this->productRepository->addTranslation($queryBuilder, $domainConfig->getLocale());
        $this->productRepository->addDomain($queryBuilder, $domainConfig->getId());
        $queryBuilder->addSelect('v')->join('pd.vat', 'v');

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
