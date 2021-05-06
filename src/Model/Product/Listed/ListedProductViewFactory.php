<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductView as BaseListedProductView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory as BaseListedProductViewFactory;

class ListedProductViewFactory extends BaseListedProductViewFactory
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     */
    public function __construct(
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        CategoryFacade $categoryFacade,
        ImageViewFacadeInterface $imageViewFacade,
        ProductActionViewFacadeInterface $productActionViewFacade,
        ProductActionViewFactory $productActionViewFactory,
        CurrentCustomerUser $currentCustomerUser,
        PriceFactory $priceFactory
    ) {
        parent::__construct(
            $domain,
            $productCachedAttributesFacade,
            $imageViewFacade,
            $productActionViewFacade,
            $productActionViewFactory,
            $currentCustomerUser,
            $priceFactory
        );

        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \App\Model\Product\Listed\ListedProductView
     */
    public function createFromProduct(Product $product, ?ImageView $imageView, ProductActionView $productActionView): BaseListedProductView
    {
        $domainId = $this->domain->getId();
        $flagIds = $this->getFlagIdsForProductForDomain($product, $domainId);

        return new ListedProductView(
            $product->getId(),
            $product->getName(),
            $product->getShortDescription($domainId),
            $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $flagIds,
            $productActionView,
            $imageView,
            $product->getNamePrefix(),
            $product->getNameSufix(),
            $this->productAvailabilityFacade->getProductAvailableStocksCountInformationByDomainId($product, $domainId),
            $this->productAvailabilityFacade->getProductCountExposedInStocksInformationByDomainId($product, $domainId),
            $this->categoryFacade->getCategoriesNamesInPathAsString(
                $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId),
                $this->domain->getLocale()
            ),
            $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromArray(array $productArray, ?ImageView $imageView, ProductActionView $productActionView, PricingGroup $pricingGroup): BaseListedProductView
    {
        return new ListedProductView(
            $productArray['id'],
            $productArray['name'],
            $productArray['short_description'],
            $productArray['availability'],
            $this->priceFactory->createProductPriceFromArrayByPricingGroup($productArray['prices'], $pricingGroup),
            $productArray['flags'],
            $productActionView,
            $imageView,
            $productArray['name_prefix'],
            $productArray['name_sufix'],
            $productArray['product_available_stocks_count_information'],
            $productArray['product_count_exposed_in_stores'],
            $productArray['main_category_path'],
            array_key_exists('is_available', $productArray) ? $productArray['is_available'] : true,
        );
    }

    /**
     * @param \App\Model\Product\Product[] $products
     * @return \App\Model\Product\Listed\ListedProductView[]
     */
    public function createFromProducts(array $products): array
    {
        $imageViews = $this->imageViewFacade->getMainImagesByEntityIds(
            Product::class,
            $this->getIdsForProducts($products)
        );
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];
        foreach ($products as $product) {
            $productId = $product->getId();
            if (!$this->productAvailabilityFacade->isProductExcludedOnDomain($product, $this->domain->getId())
                && $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder($product, $this->domain->getId())
            ) {
                $listedProductViews[$productId] = $this->createFromProduct(
                    $product,
                    $imageViews[$productId],
                    $productActionViews[$productId]
                );
            }
        }

        return $listedProductViews;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param mixed $domainId
     * @return int[]
     */
    protected function getFlagIdsForProductForDomain(Product $product, $domainId): array
    {
        $flagIds = [];
        foreach ($product->getFlagsForDomain($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }
}
