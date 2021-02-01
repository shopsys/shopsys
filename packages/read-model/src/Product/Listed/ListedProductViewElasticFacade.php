<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use BadMethodCallException;
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
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
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
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade
     * @deprecated use ProductActionViewFactory instead
     */
    protected $productActionViewFacade;

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
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory|null $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider|null $productElasticsearchProvider
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
        ImageViewFacadeInterface $imageViewFacade,
        ?ProductActionViewFactory $productActionViewFactory = null,
        ?ProductElasticsearchProvider $productElasticsearchProvider = null
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     * @deprecated since Shopsys Framework 9.1
     * @see \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory class instead
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
     * @deprecated since Shopsys Framework 9.1
     * @see \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory class instead
     */
    protected function getIdsForProducts(array $products): array
    {
        $message = 'The %s() method is deprecated since Shopsys Framework 9.1. Use ListedProductViewFactory::getIdsForProducts() instead.';
        @trigger_error(sprintf($message, __METHOD__), E_USER_DEPRECATED);

        return array_map(static function (Product $product): int {
            return $product->getId();
        }, $products);
    }

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductActionViewFactory(ProductActionViewFactory $productActionViewFactory): void
    {
        if (
            $this->productActionViewFactory !== null
            && $this->productActionViewFactory !== $productActionViewFactory
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productActionViewFactory !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productActionViewFactory = $productActionViewFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductElasticsearchProvider(ProductElasticsearchProvider $productElasticsearchProvider): void
    {
        if (
            $this->productElasticsearchProvider !== null
            && $this->productElasticsearchProvider !== $productElasticsearchProvider
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productElasticsearchProvider !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productElasticsearchProvider = $productElasticsearchProvider;
    }
}
