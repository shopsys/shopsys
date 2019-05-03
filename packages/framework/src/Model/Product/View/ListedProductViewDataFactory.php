<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;

/**
 * @experimental
 */
class ListedProductViewDataFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade
     */
    protected $productAccessoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade
     */
    protected $topProductFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer $currentCustomer
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomer $currentCustomer,
        TopProductFacade $topProductFacade,
        ProductOnCurrentDomainFacade $productOnCurrentDomainFacade,
        ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
        $this->productFacade = $productFacade;
        $this->productAccessoryFacade = $productAccessoryFacade;
        $this->domain = $domain;
        $this->currentCustomer = $currentCustomer;
        $this->topProductFacade = $topProductFacade;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
    }

    /**
     * @param int|null $limit
     * @return array
     */
    public function getForTop(int $limit = null): array
    {
        $topProducts = $this->topProductFacade->getAllOfferedProducts(
            $this->domain->getId(),
            $this->currentCustomer->getPricingGroup()
        );

        if ($limit !== null) {
            $topProducts = array_slice($topProducts, 0, $limit);
        }

        return $this->createFromProducts($topProducts);
    }

    /**
     * @param int $productId
     * @param int|null $limit
     * @return array
     */
    public function getForAccessories(int $productId, int $limit = null): array
    {
        $product = $this->productFacade->getById($productId);

        $accessories = $this->productAccessoryFacade->getTopOfferedAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomer->getPricingGroup(),
            $limit
        );

        return $this->createFromProducts($accessories);
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedForFilteredInCategory(int $categoryId, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsInCategory($filterData, $orderingModeId, $page, $limit, $categoryId);

        return $this->createPaginationResultWithData($paginationResult);
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedForFilteredSearch(?string $searchText, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsForSearch($searchText, $filterData, $orderingModeId, $page, $limit);

        return $this->createPaginationResultWithData($paginationResult);
    }

    /**
     * @param int $brandId
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedForBrand(int $brandId, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsForBrand($orderingModeId, $page, $limit, $brandId);

        return $this->createPaginationResultWithData($paginationResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return array
     */
    protected function createFromProducts(array $products): array
    {
        $listedProductViewsData = [];
        foreach ($products as $product) {
            $listedProductViewData = $this->createFromProduct($product);

            $listedProductViewsData[] = $listedProductViewData;
        }

        return $listedProductViewsData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    protected function createPaginationResultWithData(PaginationResult $paginationResult): PaginationResult
    {
        return new PaginationResult(
            $paginationResult->getPage(),
            $paginationResult->getPageSize(),
            $paginationResult->getTotalCount(),
            $this->createFromProducts($paginationResult->getResults())
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function createFromProduct(Product $product): array
    {
        $flagIds = array_map(function (Flag $flag): int {
            return $flag->getId();
        }, $product->getFlags()->toArray());

        $listedProductViewData = [];
        $listedProductViewData['name'] = $product->getName();
        $listedProductViewData['id'] = $product->getId();
        $listedProductViewData['flagIds'] = $flagIds;
        $listedProductViewData['availability'] = $product->getCalculatedAvailability()->getName();
        $listedProductViewData['shortDescription'] = $product->getShortDescription($this->domain->getId());
        $listedProductViewData['sellingDenied'] = $product->getCalculatedSellingDenied();
        $listedProductViewData['mainVariant'] = $product->isMainVariant();
        $listedProductViewData['sellingPrice'] = $this->productCachedAttributesFacade->getProductSellingPrice($product);

        return $listedProductViewData;
    }
}
