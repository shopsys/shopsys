<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductSellableInCategoryBatchLoadDataFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BestsellingProductsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly DataLoaderInterface $productsSellableInCategoryByIdsBatchLoader,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        protected readonly ProductSellableInCategoryBatchLoadDataFactory $productSellableInCategoryBatchLoadDataFactory,
    ) {
    }

    public function bestSellingProductsByCategoryQuery(
        Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix,
    ): Promise {
        $category = $categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix
            ? $categoryOrReadyCategorySeoMix->getCategory()
            : $categoryOrReadyCategorySeoMix;

        $bestsellingProductsIds = $this->cachedBestsellingProductFacade->getOfferedBestsellingProductIds(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup(),
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );

        $batchLoadData = $this->productSellableInCategoryBatchLoadDataFactory->create(
            $bestsellingProductsIds,
            $category,
        );

        return $this->productsSellableInCategoryByIdsBatchLoader->load($batchLoadData);
    }
}
