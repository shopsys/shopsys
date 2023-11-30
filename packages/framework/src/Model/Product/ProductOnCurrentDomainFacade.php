<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductOnCurrentDomainFacade implements ProductOnCurrentDomainFacadeInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountRepository $productFilterCountRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository $brandRepository
     */
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly ProductFilterCountRepository $productFilterCountRepository,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly BrandRepository $brandRepository,
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getVisibleProductById(int $productId): Product
    {
        return $this->productRepository->getVisible(
            $productId,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataInCategory(
        int $categoryId,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
        string $searchText = '',
    ): ProductFilterCountData {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $this->categoryRepository->getById($categoryId),
        );

        // database filtering does not support limit by search

        return $this->productFilterCountRepository->getProductFilterCountData(
            $productsQueryBuilder,
            $this->domain->getLocale(),
            $productFilterConfig,
            $productFilterData,
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * @param string|null $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForSearch(
        ?string $searchText,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        $productsQueryBuilder = $this->productRepository->getListableBySearchTextQueryBuilder(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $this->domain->getLocale(),
            $searchText,
        );

        return $this->productFilterCountRepository->getProductFilterCountData(
            $productsQueryBuilder,
            $this->domain->getLocale(),
            $productFilterConfig,
            $productFilterData,
            $this->currentCustomerUser->getPricingGroup(),
        );
    }
}
