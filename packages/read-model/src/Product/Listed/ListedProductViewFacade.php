<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;

class ListedProductViewFacade implements ListedProductViewFacadeInterface
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
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade
     */
    protected $topProductFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory
     */
    protected $listedProductViewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        TopProductFacade $topProductFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory
    ) {
        $this->productFacade = $productFacade;
        $this->productAccessoryFacade = $productAccessoryFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->topProductFacade = $topProductFacade;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
    }

    /**
     * @param int $limit
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getTop(int $limit): array
    {
        $topProducts = $this->topProductFacade->getAllOfferedProducts(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        $topProducts = array_slice($topProducts, 0, $limit);

        return $this->listedProductViewFactory->createFromProducts($topProducts);
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAllTop(): array
    {
        $topProducts = $this->topProductFacade->getAllOfferedProducts(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        return $this->listedProductViewFactory->createFromProducts($topProducts);
    }

    /**
     * @param int $productId
     * @param int $limit
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAccessories(int $productId, int $limit): array
    {
        $product = $this->productFacade->getById($productId);

        $accessories = $this->productAccessoryFacade->getTopOfferedAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $limit
        );

        return $this->listedProductViewFactory->createFromProducts($accessories);
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAllAccessories(int $productId): array
    {
        $product = $this->productFacade->getById($productId);

        $accessories = $this->productAccessoryFacade->getTopOfferedAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            null
        );

        return $this->listedProductViewFactory->createFromProducts($accessories);
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $filterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getFilteredPaginatedInCategory(int $categoryId, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsInCategory(
            $filterData,
            $orderingModeId,
            $page,
            $limit,
            $categoryId
        );

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
    public function getFilteredPaginatedForSearch(string $searchText, ProductFilterData $filterData, string $orderingModeId, int $page, int $limit): PaginationResult
    {
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsForSearch(
            $searchText,
            $filterData,
            $orderingModeId,
            $page,
            $limit
        );

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
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsForBrand(
            $orderingModeId,
            $page,
            $limit,
            $brandId
        );

        return $this->createPaginationResultWithData($paginationResult);
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
            $this->listedProductViewFactory->createFromProducts($paginationResult->getResults())
        );
    }
}
