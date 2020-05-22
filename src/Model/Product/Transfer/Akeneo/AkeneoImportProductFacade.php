<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\ProductFacade;
use App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade;
use DateTime;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class AkeneoImportProductFacade extends AbstractAkeneoImportTransfer
{
    /**
     * @var \App\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade
     */
    protected $productTransferAkeneoFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator
     */
    protected $productTransferAkeneoValidator;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper
     */
    protected $productTransferAkeneoMapper;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    private $productVisibilityFacade;

    /**
     * @var \DateTime|null
     */
    private $lastProductUpdatedAtFromAkeneo;

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
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportProductMainVariantFacade
     */
    private $akeneoImportProductMainVariantFacade;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor
     */
    private $transferredProductProcessor;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductMainVariantFacade $akeneoImportProductMainVariantFacade
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \App\Model\Product\Package\ProductPackageFacade $productPackageFacade
     * @param \App\Model\Product\Transfer\Akeneo\AssetTransferAkeneoFacade $assetTransferAkeneoFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        ProductFacade $productFacade,
        ProductVisibilityFacade $productVisibilityFacade,
        Setting $setting,
        AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade,
        AkeneoImportProductMainVariantFacade $akeneoImportProductMainVariantFacade,
        TransferredProductProcessor $transferredProductProcessor
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->productTransferAkeneoValidator = $productTransferAkeneoValidator;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->productFacade = $productFacade;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->setting = $setting;
        $this->akeneoImportProductParameterFacade = $akeneoImportProductParameterFacade;
        $this->akeneoImportProductGroupParameterFacade = $akeneoImportProductGroupParameterFacade;
        $this->akeneoImportProductSeriesFacade = $akeneoImportProductSeriesFacade;
        $this->akeneoImportProductMainVariantFacade = $akeneoImportProductMainVariantFacade;
        $this->transferredProductProcessor = $transferredProductProcessor;
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        $lastProductsUpdatedAt = $this->setting->get(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME);

        $this->lastProductUpdatedAtFromAkeneo = $lastProductsUpdatedAt;

        $this->logger->addInfo(sprintf('Getting data from API for search greater than last updated : %s', $lastProductsUpdatedAt->format(DATE_ATOM)));

        foreach ($this->productTransferAkeneoFacade->getAllUpdatedProductsFromLastUpdate($lastProductsUpdatedAt) as $product) {
            yield $product;
        }
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Transfer products data from Akeneo ...');

        $akeneoProductsData = $this->getData();

        $allProductSeriesCodes = [];
        $isAllParametersImported = true;
        $mainVariantSkuList = [];
        foreach ($akeneoProductsData as $akeneoProductData) {
            if ($isAllParametersImported === true) {
                $isAllParametersImported = $this->transferredProductProcessor->checkIsAllParametersExistFromAkeneoData($akeneoProductData);
            }

            $allProductSeriesCodes = array_merge(
                $allProductSeriesCodes,
                $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductSeriesCodeList($akeneoProductData)
            );

            $mainVariantSku = $this->productTransferAkeneoMapper->mapAkeneoProductDataToParentSkuList($akeneoProductData);
            if ($mainVariantSku !== null) {
                $mainVariantSkuList[] = $mainVariantSku;
            }
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

        $mainVariantSkuList = array_unique($mainVariantSkuList);
        if (count($mainVariantSkuList) > 0) {
            $this->akeneoImportProductMainVariantFacade->downloadMainVariantsBySkuList($mainVariantSkuList);
        }
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem($akeneoProductData): void
    {
        $this->transferredProductProcessor->processProduct($akeneoProductData, $this->logger);

        $this->setLastUpdatedProduct($akeneoProductData['updated']);
    }

    protected function doAfterTransfer(): void
    {
        //$this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
        $this->logger->addInfo('Transfer is done.');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();
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
        return t('Přenos produktů');
    }
}
