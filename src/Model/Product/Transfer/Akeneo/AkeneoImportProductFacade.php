<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportProductFacade extends AbstractAkeneoImportTransfer
{
    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade
     */
    private $productTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator
     */
    private $productTransferAkeneoValidator;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper
     */
    private $productTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    private $productVisibilityFacade;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        ProductFacade $productFacade,
        ProductVisibilityFacade $productVisibilityFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->productTransferAkeneoValidator = $productTransferAkeneoValidator;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->productFacade = $productFacade;
        $this->productVisibilityFacade = $productVisibilityFacade;
    }

    /**
     * @return \Generator|null
     */
    protected function getData(): ?\Generator
    {
        return $this->productTransferAkeneoFacade->getAll();
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer products data from Akeneo ...');
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem(array $akeneoProductData): void
    {
        $product = $this->productFacade->findOneByCatnumExcludeMainVariants($akeneoProductData['identifier']);
        $productData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductData($akeneoProductData, $product);

        if ($product === null) {
            $this->logger->addInfo(sprintf('Creating product catnum: %s', $productData->catnum));
            $this->productFacade->create($productData);
        } else {
            $this->logger->addInfo(sprintf('Updating product catnum: %s', $product->getCatnum()));
            $this->productFacade->edit($product->getId(), $productData);
        }
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Transfer is done.');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();
    }
}
