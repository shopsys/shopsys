<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\NoProductPriceForPricingGroupException;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;

class ListedProductViewElasticFacade implements ListedProductViewFacadeInterface
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
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory
     */
    protected $productActionViewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider
     */
    protected $productElasticsearchProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        TopProductFacade $topProductFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory,
        ImageViewFacadeInterface $imageViewFacade,
        ProductActionViewFactory $productActionViewFactory,
        ProductElasticsearchProvider $productElasticsearchProvider
    ) {
        $this->productFacade = $productFacade;
        $this->productAccessoryFacade = $productAccessoryFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->topProductFacade = $topProductFacade;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFactory = $productActionViewFactory;
        $this->productElasticsearchProvider = $productElasticsearchProvider;
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
        $productArray = $this->productElasticsearchProvider->getVisibleProductArrayById($productId);

        if (count($productArray) === 0) {
            return [];
        }

        return $this->listedProductViewFactory->createFromProductsArray(
            $this->productElasticsearchProvider->getSellableProductArrayByIds($productArray['accessories'], $limit)
        );
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAllAccessories(int $productId): array
    {
        $productArray = $this->productElasticsearchProvider->getVisibleProductArrayById($productId);

        if (count($productArray) === 0) {
            return [];
        }

        return $this->listedProductViewFactory->createFromProductsArray(
            $this->productElasticsearchProvider->getSellableProductArrayByIds($productArray['accessories'])
        );
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

        return $this->createPaginationResultWithArray($paginationResult);
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

        return $this->createPaginationResultWithArray($paginationResult);
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

        return $this->createPaginationResultWithArray($paginationResult);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    protected function createPaginationResultWithArray(PaginationResult $paginationResult): PaginationResult
    {
        return new PaginationResult(
            $paginationResult->getPage(),
            $paginationResult->getPageSize(),
            $paginationResult->getTotalCount(),
            $this->createFromArray($paginationResult->getResults())
        );
    }

    /**
     * @param array $productsArray
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    protected function createFromArray(array $productsArray): array
    {
        $imageViews = $this->imageViewFacade->getMainImagesByEntityIds(
            Product::class,
            array_column($productsArray, 'id')
        );

        $listedProductViews = [];

        foreach ($productsArray as $productArray) {
            $productId = $productArray['id'];

            try {
                $listedProductViews[$productId] = $this->listedProductViewFactory->createFromArray(
                    $productArray,
                    $imageViews[$productId],
                    $this->productActionViewFactory->createFromArray($productArray),
                    $this->currentCustomerUser->getPricingGroup()
                );
            } catch (NoProductPriceForPricingGroupException $exception) {
                continue;
            }
        }

        return $listedProductViews;
    }
}
