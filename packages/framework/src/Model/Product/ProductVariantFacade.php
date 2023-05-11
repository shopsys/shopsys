<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class ProductVariantFacade
{
    protected EntityManagerInterface $em;

    protected ProductFacade $productFacade;

    protected ProductDataFactoryInterface $productDataFactory;

    protected ImageFacade $imageFacade;

    protected ProductFactoryInterface $productFactory;

    protected ProductPriceRecalculationScheduler $productPriceRecalculationScheduler;

    protected ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler;

    protected ProductExportScheduler $productExportScheduler;

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
        EntityManagerInterface $em,
        ProductFacade $productFacade,
        ProductDataFactoryInterface $productDataFactory,
        ImageFacade $imageFacade,
        ProductFactoryInterface $productFactory,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        ProductExportScheduler $productExportScheduler
    ) {
        $this->em = $em;
        $this->productFacade = $productFacade;
        $this->productDataFactory = $productDataFactory;
        $this->imageFacade = $imageFacade;
        $this->productFactory = $productFactory;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->productExportScheduler = $productExportScheduler;
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
            $this->productAvailabilityRecalculationScheduler->cleanScheduleForImmediateRecalculation();
            $this->productPriceRecalculationScheduler->cleanScheduleForImmediateRecalculation();

            throw $exception;
        }

        $this->productExportScheduler->scheduleRowIdForImmediateExport($mainVariant->getId());

        foreach ($mainVariant->getVariants() as $variant) {
            $this->productExportScheduler->scheduleRowIdForImmediateExport($variant->getId());
        }

        return $mainVariant;
    }
}
