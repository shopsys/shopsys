<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductInputPriceFacade
{
    protected const BATCH_SIZE = 50;

    protected EntityManagerInterface $em;

    protected PricingSetting $pricingSetting;

    protected ProductManualInputPriceRepository $productManualInputPriceRepository;

    protected ProductRepository $productRepository;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    protected IterableResult|array|null $productRowsIterator = null;

    protected ProductInputPriceRecalculator $productInputPriceRecalculator;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator $productInputPriceRecalculator
     */
    public function __construct(
        EntityManagerInterface $em,
        PricingSetting $pricingSetting,
        ProductManualInputPriceRepository $productManualInputPriceRepository,
        ProductRepository $productRepository,
        ProductInputPriceRecalculator $productInputPriceRecalculator
    ) {
        $this->em = $em;
        $this->pricingSetting = $pricingSetting;
        $this->productManualInputPriceRepository = $productManualInputPriceRepository;
        $this->productRepository = $productRepository;
        $this->productInputPriceRecalculator = $productInputPriceRecalculator;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public function getManualInputPricesDataIndexedByPricingGroupId(Product $product)
    {
        $manualInputPricesDataByPricingGroupId = [];

        $manualInputPrices = $this->productManualInputPriceRepository->getByProduct($product);

        foreach ($manualInputPrices as $manualInputPrice) {
            $pricingGroupId = $manualInputPrice->getPricingGroup()->getId();
            $manualInputPricesDataByPricingGroupId[$pricingGroupId] = $manualInputPrice->getInputPrice();
        }

        return $manualInputPricesDataByPricingGroupId;
    }

    /**
     * @return bool
     */
    public function replaceBatchVatAndRecalculateInputPrices()
    {
        if ($this->productRowsIterator === null) {
            $this->productRowsIterator = $this->productRepository->getProductIteratorForReplaceVat();
        }

        for ($count = 0; $count < static::BATCH_SIZE; $count++) {
            $row = $this->productRowsIterator->next();

            if ($row === false) {
                $this->em->flush();
                $this->em->clear();

                return false;
            }

            /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
            $product = $row[0];

            $productManualInputPrices = $this->productManualInputPriceRepository->getByProduct($product);
            $inputPriceType = $this->pricingSetting->getInputPriceType();

            foreach ($productManualInputPrices as $productManualInputPrice) {
                $domainId = $productManualInputPrice->getPricingGroup()->getDomainId();
                $newVat = $product->getVatForDomain($domainId)->getReplaceWith();

                if ($newVat === null) {
                    continue;
                }

                $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent(
                    $productManualInputPrice,
                    $inputPriceType,
                    $newVat->getPercent()
                );

                $product->changeVatForDomain($newVat, $domainId);
                $product->markForExport();
            }
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }
}
