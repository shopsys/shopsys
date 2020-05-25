<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade;

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
     * @var \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor
     */
    private $transferredProductProcessor;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper
     */
    private $productTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade
     */
    private $akeneoImportProductParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade
     */
    private $akeneoImportProductGroupParameterFacade;

    /**
     * @var \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade
     */
    private $akeneoImportProductSeriesFacade;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        TransferredProductProcessor $transferredProductProcessor,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->transferredProductProcessor = $transferredProductProcessor;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->akeneoImportProductParameterFacade = $akeneoImportProductParameterFacade;
        $this->akeneoImportProductGroupParameterFacade = $akeneoImportProductGroupParameterFacade;
        $this->akeneoImportProductSeriesFacade = $akeneoImportProductSeriesFacade;
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
        $akeneoProductsData = $this->getData();

        $allProductSeriesCodes = [];
        $isAllParametersImported = true;
        foreach ($akeneoProductsData as $akeneoProductData) {
            if ($isAllParametersImported === true) {
                $isAllParametersImported = $this->transferredProductProcessor->checkIsAllParametersExistFromAkeneoData($akeneoProductData);
            }

            $allProductSeriesCodes = array_merge(
                $allProductSeriesCodes,
                $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductSeriesCodeList($akeneoProductData)
            );
        }

        if ($isAllParametersImported === false) {
            $this->logger->addInfo('Transfer lost parameters from Akeneo');
            $this->akeneoImportProductGroupParameterFacade->runTransfer();
            $this->akeneoImportProductParameterFacade->runTransfer();
        }

        if ($this->transferredProductProcessor->checkIsAllProductSeriesImported($allProductSeriesCodes) === false) {
            $this->logger->addInfo('Transfer missing Product Series from Akeneo');
            $this->akeneoImportProductSeriesFacade->runTransfer();
        }
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        foreach ($this->mainVariantSkuList as $code) {
            yield $this->productTransferAkeneoFacade->getProductModelByCode($code);
        }
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $akeneoProductData['identifier'] = $akeneoProductData['code'] ?? null;
        $this->transferredProductProcessor->processProduct($akeneoProductData, $this->logger, true);
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
