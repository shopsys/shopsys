<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class LuigisBoxProductRepository
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProducts(
        DomainConfig $domainConfig,
        PricingGroup $pricingGroup,
        ?int $lastSeekId,
        int $maxResults,
    ): iterable {
        $queryBuilder = $this->productRepository->getAllSellableQueryBuilder($domainConfig->getId(), $pricingGroup)
            ->addSelect('b, pd')
            ->leftJoin('p.brand', 'b')
            ->addSelect('v')->join('pd.vat', 'v')
            ->orderBy('IDENTITY(p.mainVariant)', 'asc')
            ->setMaxResults($maxResults);

        $this->productRepository->addTranslation($queryBuilder, $domainConfig->getLocale());

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('p.id > :lastProductId')->setParameter('lastProductId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
