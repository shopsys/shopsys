<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use App\Model\Product\Series\ProductSeriesProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade as BaseListedProductViewElasticFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory;

/**
 * Class ListedProductViewElasticFacade
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
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
     * @var \App\Model\Product\Series\ProductSeriesProductFacade
     */
    protected $productSeriesProductFacade;

    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\Series\ProductSeriesProductFacade $productSeriesProductFacade
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
        ProductSeriesProductFacade $productSeriesProductFacade
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
            $imageViewFacade
        );

        $this->categoryFacade = $categoryFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->productSeriesProductFacade = $productSeriesProductFacade;
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
        $productFilterData->minimalPrice = $product->getLowPriceWithVat($domainId)->multiply('0.9');
        $productFilterData->maximalPrice = $product->getLowPriceWithVat($domainId)->multiply('1.1');

        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);

        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductsInCategoryExcludeProduct($productFilterData, $orderingModeId, $page, $limit, $mainCategory->getId(), $product->getId());

        return $this->createPaginationResultWithArray($paginationResult);
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getListedSaleProducts(): array
    {
        $productFilterData = new ProductFilterData();
        $productsArray = $this->productOnCurrentDomainFacade->getInSaleProductsHits($productFilterData);

        return $this->createFromArray($productsArray);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    protected function createFromProducts(array $products): array
    {
        $productClassName = 'Shopsys\FrameworkBundle\Model\Product\Product';
        $imageViews = $this->imageViewFacade->getForEntityIds($productClassName, $this->getIdsForProducts($products));
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];
        foreach ($products as $product) {
            $productId = $product->getId();
            if (!$this->productAvailabilityFacade->isProductExcludedOnDomain($product, $this->domain->getId())
                && $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder($product, $this->domain->getId())
            ) {
                $listedProductViews[$productId] = $this->listedProductViewFactory->createFromProduct(
                    $product,
                    $imageViews[$productId],
                    $productActionViews[$productId]
                );
            }
        }

        return $listedProductViews;
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAvailableProductsByProductSeries($productSeries): array
    {
        $products = $this->productSeriesProductFacade->findAvailableProductsByProductSeries($productSeries);

        return $this->createFromProducts($products);
    }
}
