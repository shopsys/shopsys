<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\ProductFacade;
use DateTime;
use Generator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportProductFacade extends AbstractAkeneoImportTransfer
{
    private ?DateTime $lastProductUpdatedAtFromAkeneo = null;

    /**
     * @var string[]
     */
    private array $processedProductIdentifierList;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        protected ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        protected ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        protected ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        protected ProductFacade $productFacade,
        private ProductVisibilityFacade $productVisibilityFacade,
        protected Setting $setting,
        private AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        private AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        private TransferredProductProcessor $transferredProductProcessor,
        private AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade,
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->processedProductIdentifierList = [];
    }

    /**
     * @return \Generator
     */
    protected function getData(): Generator
    {
        $lastProductsUpdatedAt = $this->setting->get(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME);

        $this->lastProductUpdatedAtFromAkeneo = $lastProductsUpdatedAt;

        $this->logger->info(sprintf('Getting data from API for search greater than last updated : %s', $lastProductsUpdatedAt->format(DATE_ATOM)));

        foreach ($this->productTransferAkeneoFacade->getAllUpdatedProductsFromLastUpdate($lastProductsUpdatedAt) as $product) {
            yield $product;
        }
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->info('Transfer products data from Akeneo ...');

        $akeneoProductsData = $this->getData();

        $isAllParametersImported = true;

        foreach ($akeneoProductsData as $akeneoProductData) {
            if ($isAllParametersImported === true) {
                $isAllParametersImported = $this->transferredProductProcessor->checkIsAllParametersExistFromAkeneoData($akeneoProductData);
            }
        }

        if ($isAllParametersImported !== false) {
            return;
        }

        $this->logger->info('Transfer missing parameters from Akeneo');
        $this->akeneoImportProductGroupParameterFacade->runTransfer();
        $this->akeneoImportProductParameterFacade->runTransfer();
    }

    /**
     * @param mixed[] $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $product = $this->transferredProductProcessor->processProduct($akeneoProductData, $this->logger);
        $this->processedProductIdentifierList[] = $product->getCatnum();
        $this->setLastUpdatedProduct($akeneoProductData['updated']);
    }

    protected function doAfterTransfer(): void
    {
        $this->downloadProductDetails();
        $this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
        $this->logger->info('Transfer is done.');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();
    }

    private function downloadProductDetails(): void
    {
        $identifierList = array_unique($this->processedProductIdentifierList);
        $this->akeneoImportProductDetailFacade->downloadProductDetailsByIdentifierList($identifierList);
    }

    /**
     * @param string $lastUpdated
     */
    private function setLastUpdatedProduct(string $lastUpdated): void
    {
        $lastUpdatedDateTime = new DateTime($lastUpdated);

        if ($lastUpdatedDateTime > $this->lastProductUpdatedAtFromAkeneo) {
            $this->lastProductUpdatedAtFromAkeneo = $lastUpdatedDateTime;
        }
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'productTransfer';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return t('Products transfer');
    }
}
