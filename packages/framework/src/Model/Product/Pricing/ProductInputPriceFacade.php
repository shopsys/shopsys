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
    protected const int BATCH_SIZE = 50;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    protected IterableResult|array|null $productRowsIterator = null;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array<int, array<int, \Shopsys\FrameworkBundle\Component\Money\Money|null>>
     */
    public function getManualInputPricesDataIndexedByDomainIdAndPricingGroupId(Product $product): array
    {
        $manualInputPricesDataByPricingGroupId = [];

        $manualInputPrices = $this->productManualInputPriceRepository->getByProduct($product);

        foreach ($manualInputPrices as $manualInputPrice) {
            $pricingGroup = $manualInputPrice->getPricingGroup();
            $manualInputPricesDataByPricingGroupId[$pricingGroup->getDomainId()][$pricingGroup->getId()] = $manualInputPrice->getInputPrice();
        }

        return $manualInputPricesDataByPricingGroupId;
    }

    /**
     * @return bool
     */
    public function replaceBatchVatAndRecalculateInputPrices(): bool
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

            foreach ($product->getProductDomains() as $productDomain) {
                $domainId = $productDomain->getDomainId();
                $newVat = $product->getVatForDomain($domainId)->getReplaceWith();

                if ($newVat === null) {
                    continue;
                }

                $product->changeVatForDomain($newVat, $domainId);
                $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId());
            }
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }
}
