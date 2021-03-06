<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade as BaseProductVariantFacade;

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
