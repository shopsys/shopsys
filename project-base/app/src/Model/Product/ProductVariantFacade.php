<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade as BaseProductVariantFacade;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductFacade $productFacade, \App\Model\Product\ProductDataFactory $productDataFactory, \App\Component\Image\ImageFacade $imageFacade, \App\Model\Product\ProductFactory $productFactory, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler, \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler)
 * @property \App\Model\Product\ProductFactory $productFactory
 */
class ProductVariantFacade extends BaseProductVariantFacade
{
    /**
     * @param \App\Model\Product\Product $mainVariant
     * @param \App\Model\Product\Product[] $variants
     * @return \App\Model\Product\Product
     */
    public function createVariant(BaseProduct $mainVariant, array $variants): Product
    {
        $mainVariant->setAsMainVariant();
        $this->productExportScheduler->scheduleRowIdForImmediateExport($mainVariant->getId());

        foreach ($variants as $variant) {
            $mainVariant->addVariant($variant);
            $this->productExportScheduler->scheduleRowIdForImmediateExport($variant->getId());
        }
        $this->em->flush();

        return $mainVariant;
    }
}
