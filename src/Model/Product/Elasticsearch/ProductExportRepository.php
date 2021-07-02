<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository as BaseProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method int[] extractVariantIds(\App\Model\Product\Product $product)
 * @method string extractDetailUrl(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractFlags(\App\Model\Product\Product $product)
 * @method int[] extractCategories(int $domainId, \App\Model\Product\Product $product)
 * @method array extractVisibility(int $domainId, \App\Model\Product\Product $product)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method array extractParameters(string $locale, \App\Model\Product\Product $product)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method setCategoryFacade(\App\Model\Category\CategoryFacade $categoryFacade)
 * @method string getBrandUrlForDomainByProduct(\App\Model\Product\Product $product, int $domainId)
 * @method array extractAccessoriesIds(\App\Model\Product\Product $product)
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 */
class ProductExportRepository extends BaseProductExportRepository
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
     */
    private ProductPriceCalculation $productPriceCalculation;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade $brandCachedFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        ProductVisibilityRepository $productVisibilityRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ProductRepository $productRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        CategoryFacade $categoryFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        BrandCachedFacade $brandCachedFacade,
        ProductPriceCalculation $productPriceCalculation
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $productFacade,
            $friendlyUrlRepository,
            $domain,
            $productVisibilityRepository,
            $friendlyUrlFacade,
            $categoryFacade,
            $productAccessoryFacade,
            $brandCachedFacade
        );

        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->productRepository = $productRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->productPriceCalculation = $productPriceCalculation;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    protected function extractResult(BaseProduct $product, int $domainId, string $locale): array
    {
        $flagIds = $this->extractFlagsForDomain($domainId, $product);
        $categoryIds = $this->extractCategories($domainId, $product);
        $mainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
        $parameters = $this->extractParametersIncludedVariants($product, $locale, $domainId);
        $prices = $this->extractPrices($domainId, $product);
        $visibility = $this->extractVisibility($domainId, $product);
        $detailUrl = $this->extractDetailUrl($domainId, $product);
        $variantIds = $this->extractVariantIds($product);
        $searchingNames = $this->extractSearchingNames($product, $locale);
        $searchingDescriptions = $this->extractSearchingDescriptions($product, $domainId);
        $searchingCatnums = $this->extractSearchingCatnums($product);
        $searchingEans = $this->extractSearchingEans($product);
        $searchingPartnos = $this->extractSearchingPartnos($product);
        $searchingShortDescriptions = $this->extractSearchingShortDescriptions($product, $domainId);

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
                $domainId
            )->getId(),
            'main_category_path' => $this->categoryFacade->getCategoriesNamesInPathAsString($mainCategory, $locale),
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder($product, $domainId),
            'is_available' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getDomainOrderingPriority($domainId),
            'calculated_selling_denied' => $product->getCalculatedSaleExclusion($domainId),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            'availability_status' => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, $domainId),
            'is_main_variant' => $product->isMainVariant(),
            'is_variant' => $product->isVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $domainId),
            'has_preorder' => $product->hasPreorder(),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'seo_h1' => $product->getSeoH1($domainId),
            'seo_title' => $product->getSeoTitle($domainId),
            'seo_meta_description' => $product->getSeoMetaDescription($domainId),
            'accessories' => $this->extractAccessoriesIds($product),
            'name_prefix' => $product->getNamePrefix($locale),
            'name_sufix' => $product->getNameSufix($locale),
            'is_in_sale' => $product->isProductInSale($domainId) && !$product->getCalculatedSaleExclusion($domainId),
            'is_sale_exclusion' => $product->getSaleExclusion($domainId),
            'product_available_stocks_count_information' => $this->productAvailabilityFacade->getProductAvailableStocksCountInformationByDomainId($product, $domainId),
            'product_count_exposed_in_stores' => $this->productAvailabilityFacade->getProductCountExposedInStocksInformationByDomainId($product, $domainId),
            'stock_availabilities_information' => $this->extractStockAvailabilitiesInformation($product, $domainId),
            'files' => $this->productFacade->getDownloadFilesForProductByDomainConfig($product, $this->domain->getDomainConfigById($domainId)),
            'usps' => $product->getAllNonEmptyShortDescriptionUsp($domainId),
            'searching_names' => $searchingNames,
            'searching_descriptions' => $searchingDescriptions,
            'searching_catnums' => $searchingCatnums,
            'searching_eans' => $searchingEans,
            'searching_partnos' => $searchingPartnos,
            'searching_short_descriptions' => $searchingShortDescriptions,
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
                'filtering_minimal_price' => (float)$this->getMaximalVariantPriceForFilteringMinimalPrice($product, $pricingGroup)->getAmount(),
                'filtering_maximal_price' => (float)$this->getMinimalVariantPriceForFilteringMaximalPrice($product, $pricingGroup)->getAmount(),
            ];
        }

        return $prices;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    private function getMaximalVariantPriceForFilteringMinimalPrice(Product $product, PricingGroup $pricingGroup): ?Money
    {
        $price = null;
        if (!$product->isMainVariant()) {
            return $this->productPriceCalculation->calculatePrice(
                $product,
                $pricingGroup->getDomainId(),
                $pricingGroup
            )->getPriceWithVat();
        }

        foreach ($product->getVariants() as $variant) {
            $variantPrice = $this->productPriceCalculation->calculatePrice(
                $variant,
                $pricingGroup->getDomainId(),
                $pricingGroup
            )->getPriceWithVat();

            if ($price === null || $variantPrice->isGreaterThan($price)) {
                $price = $variantPrice;
            }
        }

        return $price;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getMinimalVariantPriceForFilteringMaximalPrice(Product $product, PricingGroup $pricingGroup): ?Money
    {
        $price = null;
        if (!$product->isMainVariant()) {
            return $this->productPriceCalculation->calculatePrice(
                $product,
                $pricingGroup->getDomainId(),
                $pricingGroup
            )->getPriceWithVat();
        }

        foreach ($product->getVariants() as $variant) {
            $variantPrice = $this->productPriceCalculation->calculatePrice(
                $variant,
                $pricingGroup->getDomainId(),
                $pricingGroup
            )->getPriceWithVat();
            if ($price === null || $variantPrice->isLessThan($price)) {
                $price = $variantPrice;
            }
        }

        return $price;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    private function extractSearchingCatnums(Product $product): string
    {
        if ($product->isMainVariant()) {
            $variantCatnums = [];
            $variantCatnums[] = $product->getCatnum() ?? '';
            foreach ($product->getVariants() as $variant) {
                $variantCatnums[] = $variant->getCatnum() ?? '';
            }

            return trim(implode(' ', array_unique($variantCatnums)));
        }
        return $product->getCatnum() ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    private function extractSearchingEans(Product $product): string
    {
        if ($product->isMainVariant()) {
            $variantEans = [];
            $variantEans[] = $product->getEan() ?? '';
            foreach ($product->getVariants() as $variant) {
                $variantEans[] = $variant->getEan() ?? '';
            }

            return trim(implode(' ', array_unique($variantEans)));
        }
        return $product->getEan() ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return string
     */
    private function extractSearchingPartnos(Product $product): string
    {
        if ($product->isMainVariant()) {
            $variantEans = [];
            $variantEans[] = $product->getPartno() ?? '';
            foreach ($product->getVariants() as $variant) {
                $variantEans[] = $variant->getPartno() ?? '';
            }

            return trim(implode(' ', array_unique($variantEans)));
        }
        return $product->getPartno() ?? '';
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return string
     */
    private function extractSearchingNames(Product $product, string $locale): string
    {
        if ($product->isMainVariant()) {
            $variantNames = $product->getFullname($locale);
            foreach ($product->getVariants() as $variant) {
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
            foreach ($product->getVariants() as $variant) {
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
            foreach ($product->getVariants() as $variant) {
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
            $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $domainId,
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId)
            );
        }

        foreach ($variants as $variant) {
            $flagIds = array_merge($flagIds, $variant->getFlagsIdsForDomain($domainId));
        }

        return array_values(array_unique($flagIds));
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
            $products = $this->productRepository->getAllSellableVariantsByMainVariant(
                $product,
                $domainId,
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId)
            );
        }
        $products[] = $product;

        return $this->parameterRepository->getProductParameterValuesDataByProducts($products, $locale);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return array
     */
    private function extractStockAvailabilitiesInformation(Product $product, int $domainId): array
    {
        $stockAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStocksAvailabilitiesInformationByDomainIdIndexedByStockId($product, $domainId);

        $result = [];
        foreach ($stockAvailabilitiesInformation as $item) {
            $result[] = [
                'stock_name' => $item->getStockName(),
                'stock_id' => $item->getStockId(),
                'availability_information' => $item->getAvailabilityInformation(),
                'exposed' => $item->isExposedProduct(),
                'availability_status' => $item->getAvailabilityStatus(),
            ];
        }

        return $result;
    }
}
