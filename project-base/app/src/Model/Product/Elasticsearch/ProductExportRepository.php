<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\ProductVideo\ProductVideo;
use App\Model\ProductVideo\ProductVideoTranslationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository as BaseProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider as BaseProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \App\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider $productExportFieldProvider
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method array extractResult(\App\Model\Product\Product $product, int $domainId, string $locale, string[] $fields)
 * @method int[] extractVariantIds(\App\Model\Product\Product $product)
 * @method string extractDetailUrl(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractFlags(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractCategories(int $domainId, \App\Model\Product\Product $product)
 * @method array extractParameters(string $locale, \App\Model\Product\Product $product)
 * @method array extractVisibility(int $domainId, \App\Model\Product\Product $product)
 * @method string getBrandUrlForDomainByProduct(\App\Model\Product\Product $product, int $domainId)
 * @method array extractAccessoriesIds(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForDefaultPricingGroup(\App\Model\Product\Product $mainVariant, int $domainId)
 */
class ProductExportRepository extends BaseProductExportRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade $brandCachedFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \App\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider $productExportFieldProvider
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     * @param \App\Model\ProductVideo\ProductVideoTranslationsRepository $productVideoTranslationsRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        ProductVisibilityFacade $productVisibilityFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        CategoryFacade $categoryFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        BrandCachedFacade $brandCachedFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        HreflangLinksFacade $hreflangLinksFacade,
        BaseProductExportFieldProvider $productExportFieldProvider,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        ProductRepository $productRepository,
        InMemoryCache $inMemoryCache,
        private readonly ProductPriceCalculation $productPriceCalculation,
        private readonly BreadcrumbFacade $breadcrumbFacade,
        private readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $productFacade,
            $friendlyUrlRepository,
            $productVisibilityFacade,
            $friendlyUrlFacade,
            $categoryFacade,
            $productAccessoryFacade,
            $brandCachedFacade,
            $productAvailabilityFacade,
            $hreflangLinksFacade,
            $productExportFieldProvider,
            $pricingGroupSettingFacade,
            $productRepository,
            $inMemoryCache,
        );
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @param string $field
     * @return mixed
     */
    protected function getExportedFieldValue(int $domainId, BaseProduct $product, string $locale, string $field): mixed
    {
        return match ($field) {
            BaseProductExportFieldProvider::FLAGS => $this->extractFlagsForDomain($domainId, $product),
            ProductExportFieldProvider::MAIN_CATEGORY_PATH => $this->extractMainCategoryPath($product, $domainId, $locale),
            BaseProductExportFieldProvider::PARAMETERS => $this->extractParametersIncludedVariants($product, $locale, $domainId),
            BaseProductExportFieldProvider::CALCULATED_SELLING_DENIED => $product->getCalculatedSaleExclusion($domainId),
            ProductExportFieldProvider::AVAILABILITY_STATUS => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, $domainId),
            ProductExportFieldProvider::NAME_PREFIX => $product->getNamePrefix($locale),
            ProductExportFieldProvider::NAME_SUFIX => $product->getNameSufix($locale),
            ProductExportFieldProvider::IS_SALE_EXCLUSION => $product->getSaleExclusion($domainId),
            ProductExportFieldProvider::PRODUCT_AVAILABLE_STORES_COUNT_INFORMATION => $this->productAvailabilityFacade->getProductAvailableStoresCountInformationByDomainId($product, $domainId),
            ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION => $this->extractStoreAvailabilitiesInformation($product, $domainId),
            ProductExportFieldProvider::USPS => $product->getAllNonEmptyShortDescriptionUsp($domainId),
            ProductExportFieldProvider::SEARCHING_NAMES => $this->extractSearchingNames($product, $domainId, $locale),
            ProductExportFieldProvider::SEARCHING_DESCRIPTIONS => $this->extractSearchingDescriptions($product, $domainId),
            ProductExportFieldProvider::SEARCHING_CATNUMS => $this->extractSearchingCatnums($product, $domainId),
            ProductExportFieldProvider::SEARCHING_EANS => $this->extractSearchingEans($product, $domainId),
            ProductExportFieldProvider::SEARCHING_PARTNOS => $this->extractSearchingPartnos($product, $domainId),
            ProductExportFieldProvider::SEARCHING_SHORT_DESCRIPTIONS => $this->extractSearchingShortDescriptions($product, $domainId),
            ProductExportFieldProvider::SLUG => $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_product_detail', $product->getId())->getSlug(),
            ProductExportFieldProvider::AVAILABLE_STORES_COUNT => $this->productAvailabilityFacade->getAvailableStoresCount($product, $domainId),
            ProductExportFieldProvider::RELATED_PRODUCTS => $this->extractRelatedProductsId($product),
            ProductExportFieldProvider::BREADCRUMB => $this->extractBreadcrumb($product, $domainId, $locale),
            ProductExportFieldProvider::PRODUCT_VIDEOS => array_map(function (ProductVideo $productVideo) use ($locale) {
                return [
                    'token' => $productVideo->getVideoToken(),
                    'description' => ($this->productVideoTranslationsRepository->findByProductVideoIdAndLocale($productVideo->getId(), $locale))->getDescription(),
                ];
            }, $product->getProductVideos()),
            default => parent::getExportedFieldValue($domainId, $product, $locale, $field),
        };
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @return array
     */
    protected function extractPrices(int $domainId, BaseProduct $product): array
    {
        $prices = [];
        $productSellingPrices = $this->productFacade->getAllProductSellingPricesByDomainId($product, $domainId);

        foreach ($productSellingPrices as $productSellingPrice) {
            $sellingPrice = $productSellingPrice->getSellingPrice();
            $priceFrom = false;

            if ($sellingPrice instanceof ProductPrice) {
                $priceFrom = $sellingPrice->isPriceFrom();
            }

            $pricingGroup = $productSellingPrice->getPricingGroup();
            $prices[] = [
                'pricing_group_id' => $pricingGroup->getId(),
                'price_with_vat' => (float)$sellingPrice->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$sellingPrice->getPriceWithoutVat()->getAmount(),
                'vat' => (float)$sellingPrice->getVatAmount()->getAmount(),
                'price_from' => $priceFrom,
                'filtering_minimal_price' => (float)$this->getMaximalVariantPriceForFilteringMinimalPrice($product, $pricingGroup, $domainId)->getAmount(),
                'filtering_maximal_price' => (float)$this->getMinimalVariantPriceForFilteringMaximalPrice($product, $pricingGroup, $domainId)->getAmount(),
            ];
        }

        return $prices;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    private function getMaximalVariantPriceForFilteringMinimalPrice(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): Money {
        $price = null;

        if (!$product->isMainVariant()) {
            return $this->productPriceCalculation->calculatePrice(
                $product,
                $pricingGroup->getDomainId(),
                $pricingGroup,
            )->getPriceWithVat();
        }

        $variants = $this->productRepository->getAllSellableVariantsByMainVariant($product, $domainId, $pricingGroup);

        foreach ($variants as $variant) {
            $variantPrice = $this->productPriceCalculation->calculatePrice(
                $variant,
                $pricingGroup->getDomainId(),
                $pricingGroup,
            )->getPriceWithVat();

            if ($price === null || $variantPrice->isGreaterThan($price)) {
                $price = $variantPrice;
            }
        }

        if ($price === null) {
            $price = Money::zero();
        }

        return $price;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getMinimalVariantPriceForFilteringMaximalPrice(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): Money {
        $price = null;

        if (!$product->isMainVariant()) {
            return $this->productPriceCalculation->calculatePrice(
                $product,
                $pricingGroup->getDomainId(),
                $pricingGroup,
            )->getPriceWithVat();
        }

        $variants = $this->productRepository->getAllSellableVariantsByMainVariant($product, $domainId, $pricingGroup);

        foreach ($variants as $variant) {
            $variantPrice = $this->productPriceCalculation->calculatePrice(
                $variant,
                $pricingGroup->getDomainId(),
                $pricingGroup,
            )->getPriceWithVat();

            if ($price === null || $variantPrice->isLessThan($price)) {
                $price = $variantPrice;
            }
        }

        if ($price === null) {
            $price = Money::zero();
        }

        return $price;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function extractSearchingCatnums(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantCatnums = [];
            $variantCatnums[] = $product->getCatnum();
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantCatnums[] = $variant->getCatnum();
            }

            return trim(implode(' ', array_unique($variantCatnums)));
        }

        return $product->getCatnum();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function extractSearchingEans(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantEans = [];
            $variantEans[] = $product->getEan() ?? '';
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantEans[] = $variant->getEan() ?? '';
            }

            return trim(implode(' ', array_unique($variantEans)));
        }

        return $product->getEan() ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function extractSearchingPartnos(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantEans = [];
            $variantEans[] = $product->getPartno() ?? '';
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantEans[] = $variant->getPartno() ?? '';
            }

            return trim(implode(' ', array_unique($variantEans)));
        }

        return $product->getPartno() ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @return string
     */
    private function extractSearchingNames(Product $product, int $domainId, string $locale): string
    {
        if ($product->isMainVariant()) {
            $variantNames = $product->getFullname($locale);
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantFullName = $variant->getFullname($locale);

                if ($variantFullName !== '' && strpos($variantNames, $variantFullName) === false) {
                    $variantNames .= ' ' . $variantFullName;
                }
            }

            return trim($variantNames);
        }

        return $product->getFullname($locale);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function extractSearchingDescriptions(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantDescriptions = $product->getDescription($domainId) ?? '';
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantDescription = $variant->getDescription($domainId);

                if ($variantDescription !== null && $variantDescription !== '' && strpos($variantDescriptions, $variantDescription) === false) {
                    $variantDescriptions .= ' ' . $variantDescription;
                }
            }

            return trim($variantDescriptions);
        }

        return $product->getDescription($domainId) ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function extractSearchingShortDescriptions(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantDescriptions = $product->getShortDescription($domainId) ?? '';
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantDescription = $variant->getShortDescription($domainId);

                if ($variantDescription !== null && $variantDescription !== '' && strpos($variantDescriptions, $variantDescription) === false) {
                    $variantDescriptions .= ' ' . $variantDescription;
                }
            }

            return trim($variantDescriptions);
        }

        return $product->getShortDescription($domainId) ?? '';
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlagsForDomain(int $domainId, Product $product): array
    {
        $flagIds = $product->getFlagsIdsForDomain($domainId);
        $variants = [];

        if ($product->isMainVariant() === true) {
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);
        }

        foreach ($variants as $variant) {
            $flagIds = array_merge($flagIds, $variant->getFlagsIdsForDomain($domainId));
        }

        $uniqueFlagsIds = array_unique($flagIds);
        $resultArray = array_combine($uniqueFlagsIds, $uniqueFlagsIds);
        ksort($resultArray);

        return array_values($resultArray);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    private function extractParametersIncludedVariants(Product $product, string $locale, int $domainId): array
    {
        $products = [];

        if ($product->isMainVariant() === true) {
            $products = $this->getVariantsForDefaultPricingGroup($product, $domainId);
        }
        $products[] = $product;

        $parameterValuesData = $this->parameterRepository->getProductParameterValuesDataByProducts($products, $locale);

        foreach ($parameterValuesData as $key => $parameterValueData) {
            $parameterValuesData[$key]['parameter_value_for_slider_filter'] = null;

            if ($parameterValueData['parameter_type'] === Parameter::PARAMETER_TYPE_SLIDER) {
                $parameterValuesData[$key]['parameter_value_for_slider_filter'] = $parameterValueData['parameter_value_numeric_value'];
            }
        }

        return $parameterValuesData;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return array
     */
    private function extractStoreAvailabilitiesInformation(Product $product, int $domainId): array
    {
        $storeAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId($product, $domainId);

        $result = [];

        foreach ($storeAvailabilitiesInformation as $item) {
            $result[] = [
                'store_name' => $item->getStoreName(),
                'store_id' => $item->getStoreId(),
                'availability_information' => $item->getAvailabilityInformation(),
                'availability_status' => $item->getAvailabilityStatus(),
            ];
        }

        return $result;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int[]
     */
    private function extractRelatedProductsId(Product $product): array
    {
        $relatedProductsId = [];

        foreach ($product->getRelatedProducts() as $relatedProduct) {
            $relatedProductsId[] = $relatedProduct->getId();
        }

        return $relatedProductsId;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @return array<int, array{name: string, slug: string}>
     */
    private function extractBreadcrumb(Product $product, int $domainId, string $locale): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnDomain($product->getId(), 'front_product_detail', $domainId, $locale);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @return string
     */
    private function extractMainCategoryPath(Product $product, int $domainId, string $locale): string
    {
        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);

        return $this->categoryFacade->getCategoriesNamesInPathAsString($mainCategory, $locale);
    }
}
