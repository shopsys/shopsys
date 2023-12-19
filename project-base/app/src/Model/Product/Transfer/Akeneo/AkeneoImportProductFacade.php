<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use DateTime;
use Generator;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

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
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        private readonly ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        private readonly Setting $setting,
        private readonly AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        private readonly AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        private readonly TransferredProductProcessor $transferredProductProcessor,
        private readonly AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade,
        private readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
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
     * @param array $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $product = $this->transferredProductProcessor->processProduct($akeneoProductData, $this->logger);
        $this->processedProductIdentifierList[] = $product->getCatnum();
        $this->setLastUpdatedProduct($akeneoProductData['updated']);
        $this->productRecalculationDispatcher->dispatchSingleProductId($product->getId());
    }

    protected function doAfterTransfer(): void
    {
        $this->downloadProductDetails();
        $this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
        $this->logger->info('Transfer is done.');
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
