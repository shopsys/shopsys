<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class ProductInputPriceFacade
{
    protected const BATCH_SIZE = 50;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    protected IterableResult|array|null $productRowsIterator = null;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator $productInputPriceRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductInputPriceRecalculator $productInputPriceRecalculator,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
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
                    $newVat->getPercent(),
                );

                $product->changeVatForDomain($newVat, $domainId);
                $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId());
            }
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }
}
