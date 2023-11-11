<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class ProductVariantFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactoryInterface $productDataFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly ProductFactoryInterface $productFactory,
        protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createVariant(Product $mainProduct, array $variants)
    {
        $mainProduct->checkIsNotMainVariant();

        $mainVariantData = $this->productDataFactory->createFromProduct($mainProduct);
        $mainVariant = $this->productFactory->createMainVariant($mainVariantData, $mainProduct, $variants);
        $this->em->persist($mainVariant);

        try {
            $this->em->flush();
            $this->productFacade->setAdditionalDataAfterCreate($mainVariant, $mainVariantData);
            $this->imageFacade->copyImages($mainProduct, $mainVariant);
        } catch (Exception $exception) {
            $this->productPriceRecalculationScheduler->reset();

            throw $exception;
        }

        // @todo after handling variants this may be simplified
        $this->productRecalculationDispatcher->dispatchSingleProductId($mainVariant->getId());

        foreach ($mainVariant->getVariants() as $variant) {
            $this->productRecalculationDispatcher->dispatchSingleProductId($variant->getId());
        }

        return $mainVariant;
    }
}
