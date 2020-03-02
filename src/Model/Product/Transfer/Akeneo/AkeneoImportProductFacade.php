<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade;
use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
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
     * @var \DateTime|null
     */
    private $lastProductUpdatedAtFromAkeneo;

    /**
     * @var int[]
     */
    private $processedProductFilesSetupList = [];

    /**
     * @var \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade
     */
    private $akeneoImportMediaFilesFacade;

    /**
     * @var string
     */
    private $productFilesDir;

    /**
     * @param string $productFilesDir
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoValidator $productTransferAkeneoValidator
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Component\Akeneo\Transfer\MediaFiles\AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade
     */
    public function __construct(
        string $productFilesDir,
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        ProductTransferAkeneoValidator $productTransferAkeneoValidator,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        ProductFacade $productFacade,
        ProductVisibilityFacade $productVisibilityFacade,
        Setting $setting,
        AkeneoImportMediaFilesFacade $akeneoImportMediaFilesFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);

        $this->productFilesDir = $productFilesDir;
        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->productTransferAkeneoValidator = $productTransferAkeneoValidator;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->productFacade = $productFacade;
        $this->productVisibilityFacade = $productVisibilityFacade;
        $this->setting = $setting;
        $this->akeneoImportMediaFilesFacade = $akeneoImportMediaFilesFacade;
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
    }

    /**
     * @param array $akeneoProductData
     */
    protected function processItem(array $akeneoProductData): void
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

        $this->logProductForImportFiles($product, $akeneoProductData);

        $this->setLastUpdatedProduct($akeneoProductData['updated']);
    }

    protected function doAfterTransfer(): void
    {
        //$this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
        $this->logger->addInfo('Transfer is done.');
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();

        $this->importProductFiles();
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
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function logProductForImportFiles(Product $product, array $akeneoProductData): void
    {
        //d($akeneoProductData);
        $assemblyInstruction = $akeneoProductData['values']['assembly_instruction'][0]['data'] ?? null;
        $productTypePlan = $akeneoProductData['values']['product_type_plan'][0]['data'] ?? null;
        if ($assemblyInstruction !== null || $productTypePlan !== null) {
            $this->processedProductFilesSetupList[] = [
                'productId' => $product->getId(),
                'assembly_instruction' => $assemblyInstruction,
                'product_type_plan' => $productTypePlan,
            ];
        }
    }

    private function importProductFiles(): void
    {
        foreach ($this->processedProductFilesSetupList as $productSetup) {
            $product = $this->productFacade->getById($productSetup['productId']);
            //set from productSetup
            $domainId = 1;
            if ($productSetup['assembly_instruction'] !== null) {
                $this->importProductAsset($productSetup['assembly_instruction'], $this->productFacade->getAssemblyInstructionFilename($product, $domainId));
                $product->setAssemblyInstruction(true);
            }

            if ($productSetup['product_type_plan'] !== null) {
                $this->importProductAsset($productSetup['product_type_plan'], $this->productFacade->getProductTypePlanFilename($product, $domainId));
                $product->setProductTypePlan(true);
            }
        }
    }

    /**
     * @param string $code
     * @param string $fileName
     */
    private function importProductAsset(string $code, string $fileName): void
    {
        $this->akeneoImportMediaFilesFacade->downloadMediaFile($code, $this->productFilesDir, $fileName);
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
