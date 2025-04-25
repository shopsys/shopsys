<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Product\ProductFactory $productFactory
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method \App\Model\Product\Product getById(int $productId)
 * @method \App\Model\Product\Product create(\App\Model\Product\ProductData $productData, string $priority = \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum::REGULAR)
 * @method setAdditionalDataAfterCreate(\App\Model\Product\Product $product, \App\Model\Product\ProductData $productData)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[] getAllProductPricesByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method createProductVisibilities(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product getOneByCatnumExcludeMainVariants(string $productCatnum)
 * @method \App\Model\Product\Product getByUuid(string $uuid)
 * @method editProductStockRelation(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getAllByIds(int[] $ids)
 * @method createFriendlyUrlsWhenRenamed(\App\Model\Product\Product $product, array $originalNames)
 * @method array getChangedNamesByLocale(\App\Model\Product\Product $product, array $originalNames)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[][] getAllProductPricesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product|null findByCatnum(string $catnum)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface getProductPriceForDefaultPricingGroup(\App\Model\Product\Product $product, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface getProductPriceForPricingGroup(\App\Model\Product\Product $product, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] findAllByCatnums(string[] $catnums)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade, \App\Model\Product\ProductFactory $productFactory, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactory $productAccessoryFactory, \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory $productCategoryDomainFactory, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory, \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactory $productVisibilityFactory, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher, \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade, \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade, \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoFacade $productVideoFacade)
 * @method \App\Model\Product\Product edit(int $productId, \App\Model\Product\ProductData $productData, string $priority = \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum::REGULAR)
 * @method refreshProductAccessories(\App\Model\Product\Product $product, \App\Model\Product\Product[] $accessories)
 * @method saveParameters(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData)
 */
class ProductFacade extends BaseProductFacade
{
}
