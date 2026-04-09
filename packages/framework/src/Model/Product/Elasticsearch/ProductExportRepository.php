<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFileResolver;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslationsRepository;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class ProductExportRepository
{
    protected const string VARIANTS_CACHE_NAMESPACE = 'variants';
    protected const string TOP_PRODUCTS_CACHE_NAMESPACE = 'top_products';
    protected const string VALUE_SEPARATOR = ' ';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly ProductFacade $productFacade,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductAccessoryFacade $productAccessoryFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly ProductExportFieldProvider $productExportFieldProvider,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
        protected readonly ParameterValueFileResolver $parameterValueFileResolver,
        protected readonly Domain $domain,
        protected readonly TopProductRepository $topProductRepository,
    ) {
    }

    /**
     * @param string[] $fields
     */
    public function getProductsData(
        int $domainId,
        string $locale,
        int $lastProcessedId,
        int $batchSize,
        array $fields = AbstractIndex::ALL_FIELDS,
    ): array {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($batchSize);

        return $this->getResults($queryBuilder, $fields, $domainId, $locale);
    }

    /**
     * @param int[] $productIds
     * @param string[] $fields
     */
    public function getProductsDataForIds(int $domainId, string $locale, array $productIds, array $fields): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds);

        return $this->getResults($queryBuilder, $fields, $domainId, $locale);
    }

    public function getProductTotalCountForDomain(int $domainId): int
    {
        $result = new QueryPaginator($this->createQueryBuilder($domainId));

        return $result->getTotalCount();
    }

    /**
     * @param string[] $fields
     */
    protected function extractResult(Product $product, int $domainId, string $locale, array $fields): array
    {
        $exportedResult = [];

        foreach ($fields as $field) {
            $exportedResult[$field] = $this->getExportedFieldValue($domainId, $product, $locale, $field);
        }

        $this->reset();

        return $exportedResult;
    }

    protected function getExportedFieldValue(int $domainId, Product $product, string $locale, string $field): mixed
    {
        return match ($field) {
            ProductExportFieldProvider::ID => $product->getId(),
            ProductExportFieldProvider::CATNUM => $product->getCatnum(),
            ProductExportFieldProvider::PARTNO => $product->getPartno(),
            ProductExportFieldProvider::EAN => $product->getEan(),
            ProductExportFieldProvider::NAME => $product->getName($locale),
            ProductExportFieldProvider::NAME_PREFIX => $product->getNamePrefix($locale),
            ProductExportFieldProvider::NAME_SUFFIX => $product->getNameSuffix($locale),
            ProductExportFieldProvider::DESCRIPTION => $product->getDescription($domainId),
            ProductExportFieldProvider::SHORT_DESCRIPTION => $product->getShortDescription($domainId),
            ProductExportFieldProvider::BRAND => $product->getBrand() ? $product->getBrand()->getId() : '',
            ProductExportFieldProvider::BRAND_NAME => $product->getBrand() ? $product->getBrand()->getName() : '',
            ProductExportFieldProvider::BRAND_SLUG => $this->extractBrandDetailSlug($domainId, $product),
            ProductExportFieldProvider::FLAGS => $this->extractFlags($domainId, $product),
            ProductExportFieldProvider::CATEGORIES => $this->extractCategories($domainId, $product),
            ProductExportFieldProvider::MAIN_CATEGORY_ID => $this->categoryFacade->getProductMainCategoryByDomainId(
                $product,
                $domainId,
            )->getId(),
            ProductExportFieldProvider::IN_STOCK => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            ProductExportFieldProvider::PRICES => $this->extractPrices($domainId, $product),
            ProductExportFieldProvider::SPECIAL_PRICES => $this->extractSpecialPrices($domainId, $product),
            ProductExportFieldProvider::PARAMETERS => $this->extractParametersIncludedVariants($product, $locale, $domainId),
            ProductExportFieldProvider::ORDERING_PRIORITY => $product->getOrderingPriority($domainId),
            ProductExportFieldProvider::SELLING_DENIED => $product->isCalculatedSellingDenied($domainId),
            ProductExportFieldProvider::AVAILABILITY => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            ProductExportFieldProvider::IS_MAIN_VARIANT => $product->isMainVariant(),
            ProductExportFieldProvider::IS_VARIANT => $product->isVariant(),
            ProductExportFieldProvider::SLUG => $this->friendlyUrlFacade->getMainFriendlyUrlSlug($domainId, 'front_product_detail', $product->getId()),
            ProductExportFieldProvider::VISIBILITY => $this->extractVisibility($domainId, $product),
            ProductExportFieldProvider::UUID => $product->getUuid(),
            ProductExportFieldProvider::UNIT => $product->getUnit()->getName($locale),
            ProductExportFieldProvider::STOCK_QUANTITY => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $domainId),
            ProductExportFieldProvider::IS_ALLOWED_NEGATIVE_STOCK => $product->isAllowedNegativeStock(),
            ProductExportFieldProvider::VARIANTS => $this->extractVariantIds($product),
            ProductExportFieldProvider::MAIN_VARIANT_ID => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            ProductExportFieldProvider::SEO_H1 => $product->getSeoH1($domainId),
            ProductExportFieldProvider::SEO_TITLE => $product->getSeoTitle($domainId),
            ProductExportFieldProvider::SEO_META_DESCRIPTION => $product->getSeoMetaDescription($domainId),
            ProductExportFieldProvider::ACCESSORIES => $this->extractAccessoriesIds($product),
            ProductExportFieldProvider::RELATED_PRODUCTS => $this->extractRelatedProductsIds($product),
            ProductExportFieldProvider::HREFLANG_LINKS => $this->hreflangLinksFacade->getForProduct($product, $domainId, false),
            ProductExportFieldProvider::PRODUCT_TYPE => $this->extractProductType($product, $domainId),
            ProductExportFieldProvider::PRIORITY_BY_PRODUCT_TYPE => $this->extractPriorityByProductType($product, $domainId),
            ProductExportFieldProvider::AVAILABLE_STORES_COUNT => $this->productAvailabilityFacade->getAvailableStoresCount($product, $domainId),
            ProductExportFieldProvider::STORE_AVAILABILITIES_INFORMATION => $this->extractStoreAvailabilitiesInformation($product, $domainId),
            ProductExportFieldProvider::AVAILABILITY_STATUS => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, $domainId),
            ProductExportFieldProvider::SELLING_FROM => $product->getSellingFrom()?->format('Y-m-d H:i:s'),
            ProductExportFieldProvider::PRODUCT_VIDEOS => array_map(function (ProductVideo $productVideo) use ($locale) {
                return [
                    'token' => $productVideo->getVideoToken(),
                    'description' => ($this->productVideoTranslationsRepository->findByProductVideoIdAndLocale($productVideo->getId(), $locale))->getDescription(),
                ];
            }, $product->getProductVideos()),
            ProductExportFieldProvider::VAT_PERCENT => $this->extractVat($product, $domainId),
            ProductExportFieldProvider::PROMOTION => [
                'buy_quantity' => $product->getPromotionXy($domainId)?->getBuyQuantity(),
                'free_quantity' => $product->getPromotionXy($domainId)?->getFreeQuantity(),
            ],
            ProductExportFieldProvider::SEARCHING_SEO_TITLES => $this->extractSearchingSeoTitles($product, $domainId),
            ProductExportFieldProvider::SEARCHING_SEO_H1S => $this->extractSearchingSeoH1s($product, $domainId),
            ProductExportFieldProvider::SEARCHING_SEO_META_DESCRIPTIONS => $this->extractSearchingSeoMetaDescriptions($product, $domainId),
            ProductExportFieldProvider::IS_PROMOTED => $this->isProductPromoted($product, $domainId),
            ProductExportFieldProvider::TOP_PRODUCT_POSITION => $this->getTopProductPosition($product, $domainId),

            default => throw new InvalidArgumentException(sprintf('There is no definition for exporting "%s" field to Elasticsearch', $field)),
        };
    }

    /**
     * @return int[]
     */
    protected function extractVariantIds(Product $product): array
    {
        $variantIds = [];

        foreach ($product->getVariants() as $variant) {
            $variantIds[] = $variant->getId();
        }

        return $variantIds;
    }

    protected function extractBrandDetailSlug(int $domainId, Product $product): string
    {
        if ($product->getBrand() === null) {
            return '';
        }

        return $this->friendlyUrlFacade->getMainFriendlyUrlSlug($domainId, 'front_brand_detail', $product->getBrand()->getId());
    }

    protected function extractDetailSlug(int $domainId, Product $product): string
    {
        return $this->friendlyUrlFacade->getMainFriendlyUrlSlug($domainId, 'front_product_detail', $product->getId());
    }

    protected function createQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
            ->andWhere('prv.domainId = :domainId')
            ->andWhere('prv.visible = TRUE')
            ->groupBy('p.id')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @return int[]
     */
    protected function extractFlags(int $domainId, Product $product): array
    {
        $flagIds = [];

        foreach ($product->getFlags($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @return int[]
     */
    protected function extractCategories(int $domainId, Product $product): array
    {
        $categoryIds = [];
        $categoriesIndexedByDomainId = $product->getCategoriesIndexedByDomainId();

        if (isset($categoriesIndexedByDomainId[$domainId])) {
            foreach ($categoriesIndexedByDomainId[$domainId] as $category) {
                $categoryIds[] = $category->getId();
            }
        }

        return $categoryIds;
    }

    protected function extractParametersIncludedVariants(Product $product, string $locale, int $domainId): array
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

        return $this->parameterValueFileResolver->addIconDataToParameterValuesData($parameterValuesData, $this->domain->getDomainConfigById($domainId));
    }

    protected function extractProductType(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            foreach ($this->getVariantsForDefaultPricingGroup($product, $domainId) as $variant) {
                if ($variant->getProductType() === ProductTypeEnum::TYPE_BASIC) {
                    return ProductTypeEnum::TYPE_BASIC;
                }
            }

            return ProductTypeEnum::TYPE_INQUIRY;
        }

        return $product->getProductType();
    }

    protected function extractPriorityByProductType(Product $product, int $domainId): int
    {
        $productType = $this->extractProductType($product, $domainId);

        $isProductAvailable = $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId);

        return match ($productType) {
            ProductTypeEnum::TYPE_BASIC => $isProductAvailable ? 20 : 5,
            ProductTypeEnum::TYPE_INQUIRY => $isProductAvailable ? 10 : 0,
            default => $isProductAvailable ? -100 : -200,
        };
    }

    protected function extractPrices(int $domainId, Product $product): array
    {
        $prices = [];
        $productPrices = $this->productFacade->getAllProductPricesByDomainId($product, $domainId);

        foreach ($productPrices as $productPrice) {
            $pricingGroup = $productPrice->getPricingGroup();
            $price = $productPrice->getPrice();

            $prices[] = [
                'pricing_group_id' => $pricingGroup->getId(),
                'price_with_vat' => (float)$price->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$price->getPriceWithoutVat()->getAmount(),
                'vat' => (float)$price->getVatAmount()->getAmount(),
                'price_from' => $productPrice->isPriceFrom(),
                'variant_prices' => $this->getVariantPrices($product, $pricingGroup, $domainId),
            ];
        }

        return $prices;
    }

    protected function extractSpecialPrices(int $domainId, Product $product): array
    {
        $variantIds = array_map(
            static fn (Product $variant) => $variant->getId(),
            $this->getVariantsForDefaultPricingGroup($product, $domainId),
        );

        $specialPrices = $this->specialPriceFacade->getCurrentAndFutureSpecialPrices(
            $product,
            $domainId,
            $variantIds,
        );

        $return = [];

        foreach ($specialPrices as $specialPrice) {
            $priceListId = $specialPrice->priceListId;
            $priceListName = $specialPrice->priceListName;

            if (!isset($return[$priceListId])) {
                $return[$priceListId] = [
                    'price_list_id' => $priceListId,
                    'price_list_name' => $priceListName,
                    'valid_from' => $specialPrice->validFrom->format('Y-m-d H:i:s'),
                    'valid_to' => $specialPrice->validTo->format('Y-m-d H:i:s'),
                    'prices' => [],
                ];
            }

            $return[$priceListId]['prices'][] = [
                'price_with_vat' => (float)$specialPrice->price->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$specialPrice->price->getPriceWithoutVat()->getAmount(),
                'vat' => (float)$specialPrice->price->getVatAmount()->getAmount(),
                'product_id' => $specialPrice->productId,
            ];
        }

        return array_values($return);
    }

    protected function extractVisibility(int $domainId, Product $product): array
    {
        $visibility = [];

        foreach ($this->productVisibilityFacade->findProductVisibilitiesByDomainIdAndProduct(
            $domainId,
            $product,
        ) as $productVisibility) {
            $visibility[] = [
                'pricing_group_id' => $productVisibility->getPricingGroup()->getId(),
                'visible' => $productVisibility->isVisible(),
            ];
        }

        return $visibility;
    }

    protected function extractAccessoriesIds(Product $product): array
    {
        $accessoriesIds = [];
        $accessories = $this->productAccessoryFacade->getAllAccessories($product);

        foreach ($accessories as $accessory) {
            $accessoriesIds[] = $accessory->getAccessory()->getId();
        }

        return $accessoriesIds;
    }

    /**
     * @return int[]
     */
    protected function extractRelatedProductsIds(Product $product): array
    {
        $relatedProductsIds = [];

        foreach ($product->getRelatedProducts() as $relatedProduct) {
            $relatedProductsIds[] = $relatedProduct->getId();
        }

        return $relatedProductsIds;
    }

    protected function getResults(QueryBuilder $queryBuilder, array $fields, int $domainId, string $locale): array
    {
        $query = $queryBuilder->getQuery();

        if ($fields === AbstractIndex::ALL_FIELDS) {
            $fields = $this->productExportFieldProvider->getAll();
        }

        $results = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $results[$product->getId()] = $this->extractResult($product, $domainId, $locale, $fields);
        }

        return $results;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getVariantsForDefaultPricingGroup(Product $mainVariant, int $domainId): array
    {
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);

        return $this->inMemoryCache->getOrSaveValue(
            static::VARIANTS_CACHE_NAMESPACE,
            fn () => $this->productRepository->getAllSellableVariantsByMainVariant(
                $mainVariant,
                $domainId,
                $defaultPricingGroup,
            ),
            $mainVariant->getId(),
            $defaultPricingGroup->getId(),
            $domainId,
        );
    }

    protected function reset(): void
    {
        $this->inMemoryCache->deleteAllItemsInNamespace(static::VARIANTS_CACHE_NAMESPACE);
    }

    /**
     * @return array<int, int>
     */
    protected function getTopProductPositionsForDomain(int $domainId): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::TOP_PRODUCTS_CACHE_NAMESPACE,
            fn () => $this->topProductRepository->getTopProductPositionsIndexedByProductIdForDomain($domainId),
            $domainId,
        );
    }

    protected function isProductPromoted(Product $product, int $domainId): bool
    {
        return array_key_exists($product->getId(), $this->getTopProductPositionsForDomain($domainId));
    }

    protected function getTopProductPosition(Product $product, int $domainId): ?int
    {
        return $this->getTopProductPositionsForDomain($domainId)[$product->getId()] ?? null;
    }

    protected function extractStoreAvailabilitiesInformation(Product $product, int $domainId): array
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

    protected function getVariantPrices(Product $product, PricingGroup $pricingGroup, int $domainId): array
    {
        $variantPrices = [];

        foreach ($this->productRepository->getAllSellableVariantsByMainVariant($product, $domainId, $pricingGroup) as $variant) {
            $price = $this->productPriceCalculation->calculatePrice($variant, $pricingGroup->getDomainId(), $pricingGroup);
            $variantPrices[] = [
                'variant_id' => $variant->getId(),
                'price_with_vat' => (float)$price->getPrice()->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$price->getPrice()->getPriceWithoutVat()->getAmount(),
            ];
        }

        return $variantPrices;
    }

    protected function extractVat(Product $product, int $domainId): string
    {
        return $product->getVatForDomain($domainId)->getPercent();
    }

    protected function extractSearchingSeoTitles(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantSeoTitles = [$product->getSeoTitle($domainId) ?? ''];
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantSeoTitles[] = $variant->getSeoTitle($domainId) ?? '';
            }

            return trim(implode(self::VALUE_SEPARATOR, array_unique($variantSeoTitles)));
        }

        return $product->getSeoTitle($domainId) ?? '';
    }

    protected function extractSearchingSeoH1s(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantSeoH1s = [$product->getSeoH1($domainId) ?? ''];
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantSeoH1s[] = $variant->getSeoH1($domainId) ?? '';
            }

            return trim(implode(self::VALUE_SEPARATOR, array_unique($variantSeoH1s)));
        }

        return $product->getSeoH1($domainId) ?? '';
    }

    protected function extractSearchingSeoMetaDescriptions(Product $product, int $domainId): string
    {
        if ($product->isMainVariant()) {
            $variantSeoMetaDescriptions = [$product->getSeoMetaDescription($domainId) ?? ''];
            $variants = $this->getVariantsForDefaultPricingGroup($product, $domainId);

            foreach ($variants as $variant) {
                $variantSeoMetaDescriptions[] = $variant->getSeoMetaDescription($domainId) ?? '';
            }

            return trim(implode(self::VALUE_SEPARATOR, array_unique($variantSeoMetaDescriptions)));
        }

        return $product->getSeoMetaDescription($domainId) ?? '';
    }
}
