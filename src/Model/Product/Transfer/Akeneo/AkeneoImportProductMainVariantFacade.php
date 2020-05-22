<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;

class AkeneoImportProductMainVariantFacade extends AbstractAkeneoImportTransfer
{
    /**
     * @var string[]
     */
    private $mainVariantSkuList;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade
     */
    private $productTransferAkeneoFacade;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
    }

    /**
     * @param string[] $mainVariantSkuList
     */
    public function downloadMainVariantsBySkuList(array $mainVariantSkuList): void
    {
        $this->mainVariantSkuList = $mainVariantSkuList;
        $this->runTransfer();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer main variants data from Akeneo ...');
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        //d($this->mainVariantSkuList);
        return;
        foreach ($this->mainVariantSkuList as $code) {
            yield $this->productTransferAkeneoFacade->getProductModelByCode($code);
        }
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $this->productTransferAkeneoValidator->validate($akeneoProductData);

        $product = $this->productFacade->findOneByCatnumExcludeMainVariants($akeneoProductData['identifier']);
        $productData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductData($akeneoProductData, $product);

        if ($product === null) {
            $this->logger->addInfo(sprintf('Creating product catnum: %s', $productData->catnum));
            $product = $this->productFacade->create($productData);
        } else {
            $this->logger->addInfo(sprintf('Updating product catnum: %s', $product->getCatnum()));
            $product = $this->productFacade->edit($product->getId(), $productData);
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Transfer main variants is done.');
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Přenos hlavních variant produktů');
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'mainProductVariantTransfer';
    }
}
