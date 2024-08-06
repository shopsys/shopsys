<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\DataMapper;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\FrontendApi\Model\Parameter\ParameterWithValuesFactory;
use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\ProductVideo\ProductVideo;
use App\Model\ProductVideo\ProductVideoTranslationsRepository;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper as BaseProductEntityFieldMapper;

/**
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method string|null getShortDescription(\App\Model\Product\Product $product)
 * @method string getLink(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category[] getCategories(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getAccessories(\App\Model\Product\Product $product)
 * @method string|null getDescription(\App\Model\Product\Product $product)
 * @method string|null getSeoH1(\App\Model\Product\Product $product)
 * @method string|null getSeoTitle(\App\Model\Product\Product $product)
 * @method string|null getSeoMetaDescription(\App\Model\Product\Product $product)
 * @method array{name: string, status: string} getAvailability(\App\Model\Product\Product $product)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[] getHreflangLinks(\App\Model\Product\Product $product)
 * @method int getOrderingPriority(\App\Model\Product\Product $product)
 * @method \GraphQL\Executor\Promise\Promise getAccessoriesPromise(\App\Model\Product\Product $product)
 */
class ProductEntityFieldMapper extends BaseProductEntityFieldMapper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $categoriesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $brandsBatchLoader
     * @param \App\Model\ProductVideo\ProductVideoTranslationsRepository $productVideoTranslationsRepository
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
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly DataLoaderInterface $categoriesBatchLoader,
        protected readonly DataLoaderInterface $brandsBatchLoader,
        protected readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
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
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return bool
     */
    public function isSellingDenied(BaseProduct $product): bool
    {
        return $product->getCalculatedSellingDenied() === true || $product->getSaleExclusion($this->domain->getId()) === true;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    public function getName(Product $product): string
    {
        return $product->getName($this->domain->getLocale()) ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string|null
     */
    public function getNameSuffix(Product $product): ?string
    {
        return $product->getNameSufix($this->domain->getLocale());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string|null
     */
    public function getPartNumber(Product $product): ?string
    {
        return $product->getPartno();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    public function getCatalogNumber(Product $product): string
    {
        return $product->getCatnum();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getStockQuantity(Product $product): int
    {
        return $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
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
     * @return array
     */
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
     * @param \App\Model\Product\Product $product
     * @return string
     */
    public function getSlug(Product $product): string
    {
        return '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug($this->domain->getId(), 'front_product_detail', $product->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array<int, array{store_name: string, store_id: int, availability_information: string, availability_status: string}>
     */
    public function getStoreAvailabilities(Product $product): array
    {
        $storeAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(
            $product,
            $this->domain->getId(),
        );

        $result = [];

        foreach ($storeAvailabilitiesInformation as $storeAvailabilityInformation) {
            $result[] = [
                'store_name' => $storeAvailabilityInformation->getStoreName(),
                'store_id' => $storeAvailabilityInformation->getStoreId(),
                'availability_information' => $storeAvailabilityInformation->getAvailabilityInformation(),
                'availability_status' => $storeAvailabilityInformation->getAvailabilityStatus(),
            ];
        }

        return $result;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getAvailableStoresCount(Product $product): int
    {
        return $this->productAvailabilityFacade->getAvailableStoresCount(
            $product,
            $this->domain->getId(),
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string[]
     */
    public function getUsps(Product $product): array
    {
        return $product->getAllNonEmptyShortDescriptionUsp($this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array
     */
    public function getBreadcrumb(Product $product): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnDomain(
            $product->getId(),
            'front_product_detail',
            $this->domain->getId(),
            $this->domain->getLocale(),
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getCategoriesPromise(Product $product): Promise
    {
        $categories = $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
        $categoryIds = array_map(fn (Category $category) => $category->getId(), $categories);

        return $this->categoriesBatchLoader->load($categoryIds);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getRelatedProductsPromise(Product $product): Promise
    {
        $relatedProducts = $product->getRelatedProducts();
        $relatedProductsIds = array_map(fn (Product $relatedProduct) => $relatedProduct->getId(), $relatedProducts);

        return $this->productsSellableByIdsBatchLoader->load($relatedProductsIds);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise|null
     */
    public function getBrandPromise(Product $product): ?Promise
    {
        $brand = $product->getBrand();

        return $brand !== null ? $this->brandsBatchLoader->load($brand->getId()) : null;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return array
     */
    public function getProductVideos(Product $product): array
    {
        $locale = $this->domain->getLocale();

        return array_map(function (ProductVideo $productVideo) use ($locale) {
            return [
                'token' => $productVideo->getVideoToken(),
                'description' => $this->productVideoTranslationsRepository->findByProductVideoIdAndLocale($productVideo->getId(), $locale),
            ];
        }, $product->getProductVideos());
    }
}
