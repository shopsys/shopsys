<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductView as BaseListedProductView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory as BaseListedProductViewFactory;

class ListedProductViewFactory extends BaseListedProductViewFactory
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private ParameterFacade $parameterFacade;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var mixed
     */
    private $cachedColorParameterId = true;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ParameterFacade $parameterFacade,
        CategoryFacade $categoryFacade,
        ProductFacade $productFacade
    ) {
        parent::__construct($domain, $productCachedAttributesFacade);
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->parameterFacade = $parameterFacade;
        $this->categoryFacade = $categoryFacade;
        $this->productFacade = $productFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromProduct(Product $product, ?ImageView $imageView, ProductActionView $productActionView): BaseListedProductView
    {
        $variantsParametersSetup = [];
        if ($product->isMainVariant()) {
            $variantsParametersSetup = $this->parameterFacade->getVariantsSetupForElasticByMainProduct(
                $product,
                $this->domain->getLocale(),
                $this->domain->getId()
            );
        }
        $variantsParametersSetup = $this->prepareVariantsParametersSetup($variantsParametersSetup);
        list($countColorsInVariants, $countDifferentVariants) = $this->getParametersValuesInformation($variantsParametersSetup);

        return new ListedProductView(
            $product->getId(),
            $product->getName(),
            $product->getShortDescription($this->domain->getId()),
            $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $this->domain->getId()),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->getFlagIdsForProductForDomain($product, $this->domain->getId()),
            $productActionView,
            $imageView,
            $product->isMainVariant() ? $product->getDefaultVariant()->getNamePrefix() : $product->getNamePrefix(),
            $product->isMainVariant() ? '' : $product->getNameSufix(),
            $this->getProductPriceWithVatByMoney($this->productFacade->getNonSellingPriceByProductAndDomainId($product, $this->domain->getId()) ?? Money::zero()),
            $this->productAvailabilityFacade->getProductAvailableStocksCountInformationByDomainId($product, $this->domain->getId()),
            $this->productAvailabilityFacade->getProductCountExposedInStocksInformationByDomainId($product, $this->domain->getId()),
            $variantsParametersSetup,
            $this->categoryFacade->getCategoriesNamesInPathAsString(
                $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId()),
                $this->domain->getLocale()
            ),
            $countColorsInVariants,
            $countDifferentVariants
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
        $variantsParametersSetup = $this->prepareVariantsParametersSetup($productArray['variants_parameters_setup'] ?? []);
        list($countColorsInVariants, $countDifferentVariants) = $this->getParametersValuesInformation($variantsParametersSetup);

        return new ListedProductView(
            $productArray['id'],
            $productArray['name'],
            $productArray['short_description'],
            $productArray['availability'],
            $this->getProductPriceFromArrayByPricingGroup($productArray['prices'], $pricingGroup),
            $productArray['flags'],
            $productActionView,
            $imageView,
            $productArray['name_prefix'],
            $productArray['name_sufix'],
            $this->getProductPriceWithVatByMoney($productArray['non_selling_price'] === null ? Money::zero() : Money::create((string)$productArray['non_selling_price'])),
            $productArray['product_available_stocks_count_information'],
            $productArray['product_count_exposed_in_stores'],
            $variantsParametersSetup,
            $productArray['main_category_path'],
            $countColorsInVariants,
            $countDifferentVariants
        );
    }

    /**
     * @param array $variantsParametersSetup
     * @return array
     */
    private function getParametersValuesInformation(array $variantsParametersSetup): array
    {
        if ($this->cachedColorParameterId === true) {
            $colorParameter = $this->parameterFacade->findParameterByAkeneoCode(Parameter::COLOR_PARAMETER_AKENEO_CODE);
            if ($colorParameter !== null) {
                $this->cachedColorParameterId = $colorParameter->getId();
            } else {
                $this->cachedColorParameterId = false;
            }
        }

        $colorParameterValueIds = [];
        $differentParameterValueIds = [];
        foreach ($variantsParametersSetup as $variantParametersSetup) {
            foreach ($variantParametersSetup['parameter_values_setup'] as $parameterId => $parameterValuesSetup) {
                if ($parameterId === $this->cachedColorParameterId) {
                    $colorParameterValueIds = array_merge($colorParameterValueIds, $parameterValuesSetup);
                } else {
                    $differentParameterValueIds = array_merge($differentParameterValueIds, $parameterValuesSetup);
                }
            }
        }
        $colorParameterValueIds = array_unique($colorParameterValueIds);
        $countDifferentColorsInVariants = count($colorParameterValueIds);

        $differentParameterValueIds = array_unique($differentParameterValueIds);
        $countDifferentVariants = count($differentParameterValueIds);

        return [$countDifferentColorsInVariants, $countDifferentVariants];
    }

    /**
     * @param array $originalVariantsParametersSetup
     * @return array
     */
    private function prepareVariantsParametersSetup(array $originalVariantsParametersSetup): array
    {
        $variantsParametersSetup = [];
        foreach ($originalVariantsParametersSetup as $originalVariantParametersSetup) {
            $variantId = $originalVariantParametersSetup['variant_id'];
            $variantsParametersSetup[$variantId] = $originalVariantParametersSetup;
            unset($variantsParametersSetup[$variantId]['parameter_values_setup']);
            foreach ($originalVariantParametersSetup['parameter_values_setup'] as $parameterValueSetup) {
                $variantsParametersSetup[$variantId]['parameter_values_setup'][$parameterValueSetup['parameter_id']][$parameterValueSetup['parameter_value_id']] = $parameterValueSetup['parameter_value_id'];
            }
        }

        return $variantsParametersSetup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    private function getProductPriceWithVatByMoney(Money $priceWithVat): ProductPrice
    {
        return new ProductPrice(
            new Price(
                Money::zero(),
                $priceWithVat
            ),
            false
        );
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
