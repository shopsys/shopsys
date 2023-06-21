<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductFilesDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private Domain $domain)
    {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductFilesData
     */
    public function createFromProduct(Product $product): ProductFilesData
    {
        $productFilesData = new ProductFilesData();

        foreach ($this->domain->getAllIds() as $domainId) {
            $productFilesData->assemblyInstructionCode[$domainId] = $product->getAssemblyInstructionCode($domainId);
            $productFilesData->productTypePlanCode[$domainId] = $product->getProductTypePlanCode($domainId);
        }

        return $productFilesData;
    }
}
