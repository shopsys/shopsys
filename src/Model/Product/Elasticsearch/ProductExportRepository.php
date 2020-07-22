<?php

declare(strict_types=1);

namespace App\Model\Product\Elasticsearch;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository as BaseProductExportRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @method int[] extractVariantIds(\App\Model\Product\Product $product)
 * @method string extractDetailUrl(int $domainId, \App\Model\Product\Product $product)
 * @method int[] extractFlags(\App\Model\Product\Product $product)
 * @method int[] extractCategories(int $domainId, \App\Model\Product\Product $product)
 * @method array extractPrices(int $domainId, \App\Model\Product\Product $product)
 * @method array extractVisibility(int $domainId, \App\Model\Product\Product $product)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 * @property \App\Component\Domain\Domain $domain
 * @method array extractParameters(string $locale, \App\Model\Product\Product $product)
 */
class ProductExportRepository extends BaseProductExportRepository
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
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
        ParameterFacade $parameterFacade,
        ProductRepository $productRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $productFacade,
            $friendlyUrlRepository,
            $domain,
            $productVisibilityRepository,
            $friendlyUrlFacade
        );
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productRepository = $productRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
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
        $parameters = $this->extractParametersIncludedVariants($product, $locale, $domainId);
        $prices = $this->extractPrices($domainId, $product);
        $visibility = $this->extractVisibility($domainId, $product);
        $detailUrl = $this->extractDetailUrl($domainId, $product);
        $variantIds = $this->extractVariantIds($product);
        $highPriceWithVat = $product->getHighPriceWithVat($domainId);
        $variantsParametersSetup = $product->isMainVariant() ? $this->parameterFacade->getVariantsSetupForElasticByMainProduct($product, $locale, $domainId) : null;

        return [
            'id' => $product->getId(),
            'catnum' => $product->getCatnum(),
            'partno' => $product->getPartno(),
            'ean' => $product->getEan(),
            'name' => $product->getName($locale),
            'description' => $product->getDescription($domainId),
            'short_description' => $product->getShortDescription($domainId),
            'brand' => $product->getBrand() ? $product->getBrand()->getId() : '',
            'flags' => $flagIds,
            'categories' => $categoryIds,
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getDomainOrderingPriority($domainId),
            'calculated_selling_denied' => $product->getCalculatedSellingDenied(),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            'is_main_variant' => $product->isMainVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'is_using_stock' => $product->isUsingStock(),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantity($product, $domainId),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'name_prefix' => $product->isMainVariant() ? $product->getDefaultVariant()->getNamePrefix($locale) : $product->getNamePrefix($locale),
            'name_sufix' => $product->isMainVariant() ? '' : $product->getNameSufix($locale),
            'non_selling_price' => $highPriceWithVat === null ? null : $highPriceWithVat->getAmount(),
            'is_in_sale' => $product->isProductInSale($domainId),
            'is_sale_exclusion' => $product->getSaleExclusion($domainId),
            'product_available_stocks_count_information' => $this->productAvailabilityFacade->getProductAvailableStocksCountInformationByDomainId($product, $domainId),
            'product_count_exposed_in_stores' => $this->productAvailabilityFacade->getProductCountExposedInStocksInformationByDomainId($product, $domainId),
            'variants_parameters_setup' => $variantsParametersSetup,
        ];
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlagsForDomain(int $domainId, Product $product): array
    {
        $flagIds = [];
        foreach ($product->getFlagsForDomain($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
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
}
