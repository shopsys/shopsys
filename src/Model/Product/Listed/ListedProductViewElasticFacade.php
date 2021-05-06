<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Action\ProductActionViewFactory;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade as BaseListedProductViewElasticFacade;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
 */
class ListedProductViewElasticFacade extends BaseListedProductViewElasticFacade
{
    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    protected $productAvailabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    private ProductCachedAttributesFacade $productCachedAttributesFacade;

    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        TopProductFacade $topProductFacade,
        CategoryFacade $categoryFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory,
        ProductActionViewFacade $productActionViewFacade,
        ImageViewFacade $imageViewFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ProductActionViewFactory $productActionViewFactory,
        ProductElasticsearchProvider $productElasticsearchProvider,
        ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
        parent::__construct(
            $productFacade,
            $productAccessoryFacade,
            $domain,
            $currentCustomerUser,
            $topProductFacade,
            $productOnCurrentDomainFacade,
            $listedProductViewFactory,
            $productActionViewFacade,
            $imageViewFacade,
            $productActionViewFactory,
            $productElasticsearchProvider
        );

        $this->categoryFacade = $categoryFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSimilarPaginatedProductsFormProductInCategory(
        Product $product,
        int $domainId,
        string $orderingModeId,
        int $page,
        int $limit
    ): PaginationResult {
        $productFilterData = new ProductFilterData();
        $productFilterData->minimalPrice = $this->productCachedAttributesFacade->getProductSellingPrice($product)->getPriceWithVat()->multiply('0.9');
        $productFilterData->maximalPrice = $this->productCachedAttributesFacade->getProductSellingPrice($product)->getPriceWithVat()->multiply('1.1');

        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);

        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsInCategoryExcludeProduct(
            $productFilterData,
            $orderingModeId,
            $page,
            $limit,
            $mainCategory->getId(),
            $product->getId()
        );

        return $this->createPaginationResultWithArray($paginationResult);
    }

    /**
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedSaleProducts(int $page, int $limit): PaginationResult
    {
        $productFilterData = new ProductFilterData();
        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsInSale($productFilterData, $page, $limit);

        return $this->createPaginationResultWithArray($paginationResult);
    }
}
