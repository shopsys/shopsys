<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade as BaseProductVariantFacade;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductFacade $productFacade, \App\Model\Product\ProductDataFactory $productDataFactory, \App\Component\Image\ImageFacade $imageFacade, \App\Model\Product\ProductFactory $productFactory, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher)
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
        // @todo after handling variants, this may be simplified
        $this->productRecalculationDispatcher->dispatchSingleProductId($mainVariant->getId());

        foreach ($variants as $variant) {
            $mainVariant->addVariant($variant);
            $this->productRecalculationDispatcher->dispatchSingleProductId($variant->getId());
        }
        $this->em->flush();

        return $mainVariant;
    }
}
