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
     * @var \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor
     */
    private $transferredProductProcessor;

    /**
     * @var string[]
     */
    private $processedProductIdentifierList;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportProductDetailFacade
     */
    private $akeneoImportProductDetailFacade;

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
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade
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
        TransferredProductProcessor $transferredProductProcessor,
        AkeneoImportProductDetailFacade $akeneoImportProductDetailFacade
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
        $this->transferredProductProcessor = $transferredProductProcessor;
        $this->processedProductIdentifierList = [];
        $this->akeneoImportProductDetailFacade = $akeneoImportProductDetailFacade;
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
        return t('Přenos produktů');
    }
}
