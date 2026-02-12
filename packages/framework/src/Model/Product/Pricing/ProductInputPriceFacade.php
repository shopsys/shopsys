<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Iterator;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class ProductInputPriceFacade
{
    protected const int BATCH_SIZE = 50;

    /**
     * @var \Iterator<\Shopsys\FrameworkBundle\Model\Product\Product>|null
     */
    protected ?Iterator $productRowsIterator = null;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PricingSetting $pricingSetting,
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
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

    public function replaceBatchVatAndRecalculateInputPrices(): bool
    {
        if ($this->productRowsIterator === null) {
            /** @var \Iterator<\Shopsys\FrameworkBundle\Model\Product\Product> $iterator */
            $iterator = $this->productRepository->getProductIteratorForReplaceVat();
            $this->productRowsIterator = $iterator;
        }

        for ($count = 0; $count < static::BATCH_SIZE; $count++) {
            if (!$this->productRowsIterator->valid()) {
                $this->em->flush();
                $this->em->clear();

                return false;
            }

            /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
            $product = $this->productRowsIterator->current();
            $this->productRowsIterator->next();

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
