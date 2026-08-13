<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\DataMapper;

use App\FrontendApi\Model\Parameter\ParameterWithValuesFactory;
use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFileResolver;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductSellableVariantsProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslationsRepository;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper as BaseProductEntityFieldMapper;

/**
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method string|null getShortDescription(\App\Model\Product\Product $product)
 * @method string getLink(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category[] getCategories(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityInfo getAvailability(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getAccessoriesPromise(\App\Model\Product\Product $product)
 * @method string|null getDescription(\App\Model\Product\Product $product)
 * @method string|null getSeoH1(\App\Model\Product\Product $product)
 * @method string|null getSeoTitle(\App\Model\Product\Product $product)
 * @method string|null getSeoMetaDescription(\App\Model\Product\Product $product)
 * @method int getOrderingPriority(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[] getHreflangLinks(\App\Model\Product\Product $product)
 * @method bool isVisible(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getVariants(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getVariantsCount(\App\Model\Product\Product $product)
 * @method bool isInquiryType(\App\Model\Product\Product $product)
 * @method string getProductType(\App\Model\Product\Product $product)
 * @method string|null getNameSuffix(\App\Model\Product\Product $product)
 * @method string|null getNamePrefix(\App\Model\Product\Product $product)
 * @method string getFullName(\App\Model\Product\Product $product)
 * @method int|null getStockQuantity(\App\Model\Product\Product $product)
 * @method array getStoreAvailabilities(\App\Model\Product\Product $product)
 * @method int|null getAvailableStoresCount(\App\Model\Product\Product $product)
 * @method array getProductVideos(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getSlug(\App\Model\Product\Product $product)
 * @method string getVatPercent(\App\Model\Product\Product $product)
 * @method int|null getPromotionBuyQuantity(\App\Model\Product\Product $product)
 * @method int|null getPromotionFreeQuantity(\App\Model\Product\Product $product)
 * @method bool isAllowedNegativeStock(\App\Model\Product\Product $product)
 * @method bool isSellingDenied(\App\Model\Product\Product $product)
 * @method bool isCurrentlyOutOfStock(\App\Model\Product\Product $product)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method array getParameters(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getRelatedProductsPromise(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Flag\Flag[] getFlags(\App\Model\Product\Product $product)
 * @method \DateTimeImmutable|null getExpectedRestockingDate(\App\Model\Product\Product $product)
 */
class ProductEntityFieldMapper extends BaseProductEntityFieldMapper
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        Domain $domain,
        ProductCollectionFacade $productCollectionFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        CurrentCustomerUser $currentCustomerUser,
        ParameterWithValuesFactory $parameterWithValuesFactory,
        ProductAvailabilityFacade $productAvailabilityFacade,
        HreflangLinksFacade $hreflangLinksFacade,
        ProductFrontendLimitProvider $productFrontendLimitProvider,
        DataLoaderInterface $productsSellableByIdsBatchLoader,
        ProductVisibilityFacade $productVisibilityFacade,
        DataLoaderInterface $productsVisibleByIdsBatchLoader,
        DataLoaderInterface $productsVisibleCountByIdsBatchLoader,
        ProductVideoTranslationsRepository $productVideoTranslationsRepository,
        DataLoaderInterface $productSlugBatchLoader,
        ProductStockFacade $productStockFacade,
        ProductRepository $productRepository,
        ParameterRepository $parameterRepository,
        ParameterValueFileResolver $parameterValueFileResolver,
        ProductSellableVariantsProvider $productSellableVariantsProvider,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly DataLoaderInterface $categoriesBatchLoader,
        protected readonly DataLoaderInterface $brandsBatchLoader,
    ) {
        parent::__construct(
            $domain,
            $productCollectionFacade,
            $productAccessoryFacade,
            $currentCustomerUser,
            $parameterWithValuesFactory,
            $productAvailabilityFacade,
            $hreflangLinksFacade,
            $productFrontendLimitProvider,
            $productsSellableByIdsBatchLoader,
            $productVisibilityFacade,
            $productsVisibleByIdsBatchLoader,
            $productsVisibleCountByIdsBatchLoader,
            $productVideoTranslationsRepository,
            $productSlugBatchLoader,
            $productStockFacade,
            $productRepository,
            $parameterRepository,
            $parameterValueFileResolver,
            $productSellableVariantsProvider,
        );
    }

    public function getName(Product $product): string
    {
        return $product->getName($this->domain->getLocale()) ?? '';
    }

    public function getPartNumber(Product $product): ?string
    {
        return $product->getPartno();
    }

    public function getCatalogNumber(Product $product): string
    {
        return $product->getCatnum();
    }

    /**
     * @return string[]
     */
    public function getUsps(Product $product): array
    {
        return $product->getAllNonEmptyShortDescriptionUsp($this->domain->getId());
    }

    public function getBreadcrumb(Product $product): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnDomain(
            $product->getId(),
            'front_product_detail',
            $this->domain->getId(),
            $this->domain->getLocale(),
        );
    }

    public function getCategoriesPromise(Product $product): Promise
    {
        $categories = $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
        $categoryIds = array_map(fn (Category $category) => $category->getId(), $categories);

        return $this->categoriesBatchLoader->load($categoryIds);
    }

    public function getBrandPromise(Product $product): ?Promise
    {
        $brand = $product->getBrand();

        return $brand !== null ? $this->brandsBatchLoader->load($brand->getId()) : null;
    }
}
