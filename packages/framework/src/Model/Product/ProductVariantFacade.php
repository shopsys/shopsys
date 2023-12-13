<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class ProductVariantFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactoryInterface $productDataFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly ProductFactoryInterface $productFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
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
