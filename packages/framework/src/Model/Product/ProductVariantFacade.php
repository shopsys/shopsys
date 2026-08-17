<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class ProductVariantFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    public function createVariant(Product $mainProduct, array $variants): Product
    {
        $mainProduct->setAsMainVariant();

        foreach ($variants as $variant) {
            $mainProduct->addVariant($variant);
        }
        $this->em->flush();

        // variants are recalculated automatically
        $this->productRecalculationDispatcher->dispatchSingleProductId($mainProduct->getId());

        return $mainProduct;
    }
}
