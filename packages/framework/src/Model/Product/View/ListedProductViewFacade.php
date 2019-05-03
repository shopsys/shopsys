<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

/**
 * @experimental
 */
class ListedProductViewFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\View\ListedProductViewDataFactory
     */
    protected $listedProductViewDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\View\ListedProductViewFactory
     */
    protected $listedProductViewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ListedProductViewDataFactory $listedProductViewDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ListedProductViewFactory $listedProductViewFactory
     */
    public function __construct(
        ListedProductViewDataFactory $listedProductViewDataFactory,
        ListedProductViewFactory $listedProductViewFactory
    ) {
        $this->listedProductViewDataFactory = $listedProductViewDataFactory;
        $this->listedProductViewFactory = $listedProductViewFactory;
    }


    /**
     * @param int|null $limit Returns all products when "null" is provided
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ListedProductView[]
     */
    public function getTop(int $limit = null): array
    {
        $listedProductViewsData = $this->listedProductViewDataFactory->getForTop($limit);

        return $this->listedProductViewFactory->getListedProductViews($listedProductViewsData);
    }

    /**
     * @param int $productId
     * @param int|null $limit Returns all products when "null" is provided
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ListedProductView[]
     */
    public function getAccessories(int $productId, int $limit = null): array
    {
        $listedProductViewsData = $this->listedProductViewDataFactory->getForAccessories($productId, $limit);

        return $this->listedProductViewFactory->getListedProductViews($listedProductViewsData);
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param string $orderingModeId {@see Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig}
     * @param int $page Page number (starting with 1)
     * @param int $limit Number of products per page (must be greater than 0)
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getFilteredPaginatedInCategory(int $categoryId, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $listedProductViewsDataPaginationResult = $this->listedProductViewDataFactory->getPaginatedForFilteredInCategory(
            $categoryId,
            $filterData,
            $orderingModeId,
            $page,
            $limit
        );

        return $this->getPaginationResultWithViews($listedProductViewsDataPaginationResult);
    }

    /**
     * @param string|null $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param string $orderingModeId {@see Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig}
     * @param int $page Page number (starting with 1)
     * @param int $limit Number of products per page (must be greater than 0)
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getFilteredPaginatedForSearch(?string $searchText, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $listedProductViewsDataPaginationResult = $this->listedProductViewDataFactory->getPaginatedForFilteredSearch(
            $searchText,
            $filterData,
            $orderingModeId,
            $page,
            $limit
        );

        return $this->getPaginationResultWithViews($listedProductViewsDataPaginationResult);
    }

    /**
     * @param int $brandId
     * @param string $orderingModeId {@see Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig}
     * @param int $page Page number (starting with 1)
     * @param int $limit Number of products per page (must be greater than 0)
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedForBrand(int $brandId, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $listedProductViewsDataPaginationResult = $this->listedProductViewDataFactory->getPaginatedForBrand($brandId, $orderingModeId, $page, $limit);

        return $this->getPaginationResultWithViews($listedProductViewsDataPaginationResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $listedProductViewsDataPaginationResult
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    protected function getPaginationResultWithViews(PaginationResult $listedProductViewsDataPaginationResult): PaginationResult
    {
        return new PaginationResult(
            $listedProductViewsDataPaginationResult->getPage(),
            $listedProductViewsDataPaginationResult->getPageSize(),
            $listedProductViewsDataPaginationResult->getTotalCount(),
            $this->listedProductViewFactory->getListedProductViews($listedProductViewsDataPaginationResult->getResults())
        );
    }
}
