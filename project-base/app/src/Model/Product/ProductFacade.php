<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Product\ProductFactory $productFactory
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method \App\Model\Product\Product getById(int $productId)
 * @method \App\Model\Product\Product create(\App\Model\Product\ProductData $productData, string $priority = \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum::REGULAR)
 * @method void setAdditionalDataAfterCreate(\App\Model\Product\Product $product, \App\Model\Product\ProductData $productData)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[] getAllProductPricesByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method void createProductVisibilities(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product getByUuid(string $uuid)
 * @method void editProductStockRelation(\App\Model\Product\ProductData $productData, \App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getAllByIds(int[] $ids)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface[][] getAllProductPricesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product|null findByCatnum(string $catnum)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface getProductPriceForDefaultPricingGroup(\App\Model\Product\Product $product, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface getProductPriceForPricingGroup(\App\Model\Product\Product $product, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @method \App\Model\Product\Product[] findAllByCatnums(string[] $catnums)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade, \App\Model\Product\ProductFactory $productFactory, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactory $productAccessoryFactory, \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory $productCategoryDomainFactory, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory, \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactory $productVisibilityFactory, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher, \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade, \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade, \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoFacade $productVideoFacade, \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory $productPromotionXyFactory, \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory $productPromotionXyDataFactory, \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository $productPromotionXyRepository)
 * @method \App\Model\Product\Product edit(int $productId, \App\Model\Product\ProductData $productData, string $priority = \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum::REGULAR)
 * @method void refreshProductAccessories(\App\Model\Product\Product $product, \App\Model\Product\Product[] $accessories)
 * @method void saveParameters(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData)
 * @method void refreshProductPromotions(\App\Model\Product\Product $product, \App\Model\Product\ProductData $productData)
 * @method array getCalculatedSellingDeniedPerDomainIds(\App\Model\Product\Product $product)
 * @method void refreshProductPromotionForDomain(\Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData $promotionData, \App\Model\Product\Product $product, int $domainId)
 */
class ProductFacade extends BaseProductFacade
{
}
