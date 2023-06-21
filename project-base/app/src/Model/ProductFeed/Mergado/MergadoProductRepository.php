<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado;

use App\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class MergadoProductRepository
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     */
    public function __construct(protected ProductRepository $productRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \App\Model\Product\Product[]|\Doctrine\Common\Collections\Collection
     */
    public function getProducts(
        DomainConfig $domainConfig,
        PricingGroup $pricingGroup,
        ?int $lastSeekId,
        int $maxResults,
    ): iterable {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($domainConfig->getId(), $pricingGroup)
            ->addSelect('b')->leftJoin('p.brand', 'b')
            ->andWhere('p.variantType != :variantTypeMain')->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN)
            ->orderBy('p.id', 'asc')
            ->setMaxResults($maxResults);

        $this->productRepository->filterTemporaryExcludedProducts($queryBuilder, $domainConfig->getId());
        $this->productRepository->addTranslation($queryBuilder, $domainConfig->getLocale());
        $this->productRepository->addDomain($queryBuilder, $domainConfig->getId());
        $queryBuilder->andWhere('pd.calculatedSaleExclusion = FALSE');

        $queryBuilder->addSelect('v')->join('pd.vat', 'v');

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
