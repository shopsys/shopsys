<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class TopProductFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TopProductRepository $topProductRepository,
        protected readonly TopProductFactory $topProductFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll(int $domainId): array
    {
        return $this->topProductRepository->getAll($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProducts(int $domainId, PricingGroup $pricingGroup, ?int $limit): array
    {
        return $this->topProductRepository->getOfferedProductsForTopProductsOnDomain($domainId, $pricingGroup, $limit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function saveTopProductsForDomain(int $domainId, array $products): void
    {
        $oldTopProducts = $this->topProductRepository->getAll($domainId);

        $affectedProductIds = [];

        foreach ($oldTopProducts as $oldTopProduct) {
            $affectedProductIds[] = $oldTopProduct->getProduct()->getId();
            $this->em->remove($oldTopProduct);
        }
        $this->em->flush();

        $position = 1;

        foreach ($products as $product) {
            $topProduct = $this->topProductFactory->create($product, $domainId, $position++);
            $this->em->persist($topProduct);
            $affectedProductIds[] = $product->getId();
        }
        $this->em->flush();

        $this->productRecalculationDispatcher->dispatchProductIds(
            $affectedProductIds,
            exportScopes: [ProductExportScopeConfig::SCOPE_TOP_PRODUCT],
        );

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_QUERY_KEY_PART);
    }
}
