<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountRepository;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade as BaseProductOnCurrentDomainFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Product\Product getVisibleProductById(int $productId)
 * @method \App\Model\Product\Product[] getAccessoriesForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForProduct(\App\Model\Product\Product $product)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsInCategory(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsForSearch(string $searchText, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInCategory(int $categoryId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForSearch(string|null $searchText, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @property \App\Model\Product\Brand\BrandRepository $brandRepository
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class ProductOnCurrentDomainFacade extends BaseProductOnCurrentDomainFacade
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountRepository $productFilterCountRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Model\Product\Brand\BrandRepository $brandRepository
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CategoryRepository $categoryRepository,
        ProductFilterCountRepository $productFilterCountRepository,
        ProductAccessoryRepository $productAccessoryRepository,
        BrandRepository $brandRepository,
        private ProductFilterDataFactory $productFilterDataFactory,
    ) {
        parent::__construct(
            $productRepository,
            $domain,
            $currentCustomerUser,
            $categoryRepository,
            $productFilterCountRepository,
            $productAccessoryRepository,
            $brandRepository,
        );
    }

    /**
     * @param string[] $productCatnums
     * @return \App\Model\Product\Product[]
     */
    public function getVisibleProductsByCatnums(array $productCatnums): array
    {
        return $this->productRepository->getVisibleProductsByCatnumsAndDomainId(
            $productCatnums,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param string|null $searchText
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteProducts(?string $searchText, int $limit): PaginationResult
    {
        $emptyProductFilterData = $this->productFilterDataFactory->create();

        $page = 1;

        return $this->productRepository->getPaginationResultForSearchListable(
            $searchText,
            $this->domain->getId(),
            $this->domain->getLocale(),
            $emptyProductFilterData,
            ProductListOrderingConfig::ORDER_BY_RELEVANCE,
            $this->currentCustomerUser->getPricingGroup(),
            $page,
            $limit,
        );
    }
}
