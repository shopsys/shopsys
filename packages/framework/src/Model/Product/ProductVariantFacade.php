<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class ProductVariantFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactoryInterface $productDataFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly ProductFactoryInterface $productFactory,
        protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        protected readonly ProductExportScheduler $productExportScheduler,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createVariant(Product $mainProduct, array $variants): \Shopsys\FrameworkBundle\Model\Product\Product
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
            $this->productAvailabilityRecalculationScheduler->cleanScheduleForImmediateRecalculation();
            $this->productPriceRecalculationScheduler->reset();

            throw $exception;
        }

        $this->productExportScheduler->scheduleRowIdForImmediateExport($mainVariant->getId());

        foreach ($mainVariant->getVariants() as $variant) {
            $this->productExportScheduler->scheduleRowIdForImmediateExport($variant->getId());
        }

        return $mainVariant;
    }
}
