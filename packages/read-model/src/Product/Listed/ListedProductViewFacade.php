<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;

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
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacade
     * @deprecated since Shopsys Framework 9.1
     * @see \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory class instead
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade
     * @deprecated since Shopsys Framework 9.1
     * @see \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory class instead
     */
    protected $productActionViewFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        TopProductFacade $topProductFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory,
        ProductActionViewFacade $productActionViewFacade,
        ImageViewFacade $imageViewFacade
    ) {
        $this->productFacade = $productFacade;
        $this->productAccessoryFacade = $productAccessoryFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->topProductFacade = $topProductFacade;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
        $this->productActionViewFacade = $productActionViewFacade;
        $this->imageViewFacade = $imageViewFacade;
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

        return $this->createFromProducts($topProducts);
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

        return $this->createFromProducts($topProducts);
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

        return $this->createFromProducts($accessories);
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
            $this->createFromProducts($paginationResult->getResults())
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     * @deprecated since Shopsys Framework 9.1, use ListedProductViewFactory::createFromProducts() instead
     */
    protected function createFromProducts(array $products): array
    {
        $message = 'The %s() method is deprecated since Shopsys Framework 9.1. Use ListedProductViewFactory::createFromProducts() instead.';
        @trigger_error(sprintf($message, __METHOD__), E_USER_DEPRECATED);

        return $this->listedProductViewFactory->createFromProducts($products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return int[]
     * @deprecated since Shopsys Framework 9.1, use ListedProductViewFactory::getIdsForProducts() class instead
     */
    protected function getIdsForProducts(array $products): array
    {
        $message = 'The %s() method is deprecated since Shopsys Framework 9.1. Use ListedProductViewFactory::getIdsForProducts() instead.';
        @trigger_error(sprintf($message, __METHOD__), E_USER_DEPRECATED);

        return array_map(static function (Product $product): int {
            return $product->getId();
        }, $products);
    }
}
