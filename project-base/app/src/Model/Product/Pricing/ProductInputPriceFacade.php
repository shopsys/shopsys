<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade as BaseProductInputPriceFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][]|null $productRowsIterator
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator $productInputPriceRecalculator)
 * @method \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] getManualInputPricesDataIndexedByPricingGroupId(\App\Model\Product\Product $product)
 */
class ProductInputPriceFacade extends BaseProductInputPriceFacade
{
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

            /** @var \App\Model\Product\Product $product */
            $product = $row[0];

            foreach ($product->getProductDomains() as $productDomain) {
                $domainId = $productDomain->getDomainId();
                $newVat = $product->getVatForDomain($domainId)->getReplaceWith();
                $product->changeVatForDomain($newVat, $domainId);
                $product->markForExport();
            }
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }
}
