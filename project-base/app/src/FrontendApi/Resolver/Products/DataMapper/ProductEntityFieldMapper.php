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
use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
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
 * @method \App\Model\Category\Category[] getCategories( $product)
 * @method array{name: string, status: string} getAvailability(\App\Model\Product\Product $product)
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
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat getVat(\App\Model\Product\Product $product)
 * @method string getSlug(\App\Model\Product\Product $product)
 * @method string getVatPercent(\App\Model\Product\Product $product)
 * @method int|null getPromotionBuyQuantity(\App\Model\Product\Product $product)
 * @method int|null getPromotionFreeQuantity(\App\Model\Product\Product $product)
 * @method bool isAllowedNegativeStock(\App\Model\Product\Product $product)
 * @method bool isSellingDenied(\App\Model\Product\Product $product)
 * @method bool isCurrentlyOutOfStock(\App\Model\Product\Product $product)
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
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductStockFacade $productStockFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ParameterRepository $parameterRepository,
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
            $friendlyUrlFacade,
            $productStockFacade,
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
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlags(Product $product): array
    {
        $flags = $product->getFlags($this->domain->getId());

        $flagsIndexedById = [];

        foreach ($flags as $flag) {
            $flagsIndexedById[$flag->getId()] = $flag;
        }

        $variants = [];

        if ($product->isMainVariant() === true) {
            $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $this->domain->getId(),
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($this->domain->getId()),
            );
        }

        foreach ($variants as $variant) {
            $variantFlags = $variant->getFlags($this->domain->getId());

            foreach ($variantFlags as $variantFlag) {
                $flagsIndexedById[$variantFlag->getId()] = $variantFlag;
            }
        }
        ksort($flagsIndexedById);

        return array_values($flagsIndexedById);
    }

    /**
     * Method is overridden, so it returns parameters for the variants too.
     *
     * @param \App\Model\Product\Product $product
     */
    #[Override]
    public function getParameters(BaseProduct $product): array
    {
        $products = [];

        if ($product->isMainVariant() === true) {
            $products = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $this->domain->getId(),
                $this->currentCustomerUser->getPricingGroup(),
            );
        }
        $products[] = $product;

        $productParameterValuesData = $this->parameterRepository->getProductParameterValuesDataByProducts($products, $this->domain->getLocale());

        return $this->parameterWithValuesFactory->createParametersArrayFromProductArray(['parameters' => $productParameterValuesData]);
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

    public function getRelatedProductsPromise(Product $product): Promise
    {
        $relatedProducts = $product->getRelatedProducts();
        $relatedProductsIds = array_map(fn (Product $relatedProduct) => $relatedProduct->getId(), $relatedProducts);

        return $this->productsSellableByIdsBatchLoader->load($relatedProductsIds);
    }

    public function getBrandPromise(Product $product): ?Promise
    {
        $brand = $product->getBrand();

        return $brand !== null ? $this->brandsBatchLoader->load($brand->getId()) : null;
    }
}
