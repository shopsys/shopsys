<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\ProductVideo\ProductVideo;
use App\Model\ProductVideo\ProductVideoTranslationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository as BaseProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method int[] extractVariantIds(\App\Model\Product\Product $product)
 * @method string extractDetailUrl(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractFlags(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractCategories(int $domainId, \App\Model\Product\Product $product)
 * @method array extractVisibility(int $domainId, \App\Model\Product\Product $product)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 * @method array extractParameters(string $locale, \App\Model\Product\Product $product)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method string getBrandUrlForDomainByProduct(\App\Model\Product\Product $product, int $domainId)
 * @method array extractAccessoriesIds(\App\Model\Product\Product $product)
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @method mixed getExportedFieldValue(int $domainId, \App\Model\Product\Product $product, string $locale, string $field)
 */
class ProductExportRepository extends BaseProductExportRepository
{
    /**
     * @var \App\Model\Product\Product[]|null
     */
    private ?array $variantCache = null;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade $brandCachedFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider $productExportFieldProvider
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
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
        ProductExportFieldProvider $productExportFieldProvider,
        private readonly ProductRepository $productRepository,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
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
        );
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $lastProcessedId
     * @param int $batchSize
     * @param string[] $fields
     * @return array
     */
    public function getProductsData(
        int $domainId,
        string $locale,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($batchSize);

        $query = $queryBuilder->getQuery();

        $results = [];
        /** @var \App\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $results[$product->getId()] = $this->extractResult($product, $domainId, $locale, $fields);
            $this->clearVariantCache();
        }

        return $results;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int[] $productIds
     * @param string[] $fields
     * @return array
     */
    public function getProductsDataForIds(int $domainId, string $locale, array $productIds, array $fields): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds);

        $query = $queryBuilder->getQuery();

        $result = [];
        /** @var \App\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $result[$product->getId()] = $this->extractResult($product, $domainId, $locale, $fields);
            $this->clearVariantCache();
        }

        return $result;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @param string[] $fields
     * @return array
     */
    protected function extractResult(BaseProduct $product, int $domainId, string $locale, array $fields): array
    {
        $flagIds = $this->extractFlagsForDomain($domainId, $product);
        $categoryIds = $this->extractCategories($domainId, $product);
        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
        $parameters = $this->extractParametersIncludedVariants($product, $locale, $domainId);
        $prices = $this->extractPrices($domainId, $product);
        $visibility = $this->extractVisibility($domainId, $product);
        $detailUrl = $this->extractDetailUrl($domainId, $product);
        $variantIds = $this->extractVariantIds($product);
        $searchingNames = $this->extractSearchingNames($product, $domainId, $locale);
        $searchingDescriptions = $this->extractSearchingDescriptions($product, $domainId);
        $searchingCatnums = $this->extractSearchingCatnums($product, $domainId);
        $searchingEans = $this->extractSearchingEans($product, $domainId);
        $searchingPartnos = $this->extractSearchingPartnos($product, $domainId);
        $searchingShortDescriptions = $this->extractSearchingShortDescriptions($product, $domainId);
        $relatedProductsId = $this->extractRelatedProductsId($product);

        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_product_detail', $product->getId());

        return [
            'id' => $product->getId(),
            'catnum' => $product->getCatnum(),
            'partno' => $product->getPartno(),
            'ean' => $product->getEan(),
            'name' => $product->getName($locale),
            'description' => $product->getDescription($domainId),
            'short_description' => $product->getShortDescription($domainId),
            'brand' => $product->getBrand() ? $product->getBrand()->getId() : '',
            'brand_name' => $product->getBrand() ? $product->getBrand()->getName() : '',
            'brand_url' => $this->getBrandUrlForDomainByProduct($product, $domainId),
            'flags' => $flagIds,
            'categories' => $categoryIds,
            'main_category_id' => $this->categoryFacade->getProductMainCategoryByDomainId(
                $product,
                $domainId,
            )->getId(),
            'main_category_path' => $this->categoryFacade->getCategoriesNamesInPathAsString($mainCategory, $locale),
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            'is_available' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getOrderingPriority($domainId),
            'calculated_selling_denied' => $product->getCalculatedSaleExclusion($domainId),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            'availability_status' => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, $domainId)->value,
            'availability_dispatch_time' => $this->productAvailabilityFacade->getProductAvailabilityDaysByDomainId($product, $domainId),
            'is_main_variant' => $product->isMainVariant(),
            'is_variant' => $product->isVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $domainId),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'seo_h1' => $product->getSeoH1($domainId),
            'seo_title' => $product->getSeoTitle($domainId),
            'seo_meta_description' => $product->getSeoMetaDescription($domainId),
            'accessories' => $this->extractAccessoriesIds($product),
            'name_prefix' => $product->getNamePrefix($locale),
            'name_sufix' => $product->getNameSufix($locale),
            'is_sale_exclusion' => $product->getSaleExclusion($domainId),
            'product_available_stores_count_information' => $this->productAvailabilityFacade->getProductAvailableStoresCountInformationByDomainId($product, $domainId),
            'store_availabilities_information' => $this->extractStoreAvailabilitiesInformation($product, $domainId),
            'usps' => $product->getAllNonEmptyShortDescriptionUsp($domainId),
            'searching_names' => $searchingNames,
            'searching_descriptions' => $searchingDescriptions,
            'searching_catnums' => $searchingCatnums,
            'searching_eans' => $searchingEans,
            'searching_partnos' => $searchingPartnos,
            'searching_short_descriptions' => $searchingShortDescriptions,
            'slug' => $mainFriendlyUrl->getSlug(),
            'available_stores_count' => $this->productAvailabilityFacade->getAvailableStoresCount($product, $domainId),
            'related_products' => $relatedProductsId,
            'breadcrumb' => $this->extractBreadcrumb($product, $domainId, $locale),
            'product_videos' => array_map(function (ProductVideo $productVideo) use ($locale) {
                return [
                    'token' => $productVideo->getVideoToken(),
                    'description' => ($this->productVideoTranslationsRepository->findByProductVideoIdAndLocale($productVideo->getId(), $locale))->getDescription(),
                ];
            }, $product->getProductVideos()),
            'hreflang_links' => $this->hreflangLinksFacade->getForProduct($product, $domainId),
        ];
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
            if ($parameterValueData['parameter_type'] === Parameter::PARAMETER_TYPE_SLIDER) {
                $parameterValuesData[$key]['parameter_value_for_slider_filter'] = (float)$parameterValueData['parameter_value_text'];
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
                'availability_status' => $item->getAvailabilityStatus()->value,
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
     * @param \App\Model\Product\Product $mainVariant
     * @param int $domainId
     * @return \App\Model\Product\Product[]
     */
    private function getVariantsForDefaultPricingGroup(Product $mainVariant, int $domainId): array
    {
        if ($this->variantCache === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
            $this->variantCache = $this->productRepository->getAllSellableVariantsByMainVariant($mainVariant, $domainId, $pricingGroup);
        }

        return $this->variantCache;
    }

    private function clearVariantCache(): void
    {
        $this->variantCache = null;
    }
}
