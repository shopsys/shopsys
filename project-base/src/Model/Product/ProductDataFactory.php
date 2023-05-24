<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;

/**
 * @method \App\Model\Product\ProductData create()
 * @method \App\Model\Product\ProductData createFromProduct(\App\Model\Product\Product $product)
 * @method fillNew(\App\Model\Product\ProductData $productData)
 * @method fillFromProduct(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getAccessoriesData(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] getParametersData(\App\Model\Product\Product $product)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade, \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade, \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory)
 */
class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        $productData = new ProductData();
        $productData->images = $this->imageUploadDataFactory->create();

        return $productData;
    }
}
