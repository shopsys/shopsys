<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Component\Setting\Setting;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
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
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade
     */
    private $akeneoImportProductParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade
     */
    private $akeneoImportProductGroupParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

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
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
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
        ParameterFacade $parameterFacade
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
        $this->parameterFacade = $parameterFacade;
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

        foreach ($akeneoProductsData as $akeneoProductData) {
            $isAllParametersImported = $this->checkIsAllParametersExistFromAkeneoData($akeneoProductData);
            if ($isAllParametersImported === false) {
                $this->logger->addInfo('Transfer lost parameters from Akeneo');
                $this->akeneoImportProductGroupParameterFacade->runTransfer();
                $this->akeneoImportProductParameterFacade->runTransfer();
                break;
            }
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

        $this->setProductForImportFiles($product, $akeneoProductData);

        $this->setLastUpdatedProduct($akeneoProductData['updated']);
    }

    protected function doAfterTransfer(): void
    {
        $this->setting->set(Setting::AKENEO_TRANSFER_PRODUCTS_LAST_UPDATED_DATETIME, $this->lastProductUpdatedAtFromAkeneo);
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
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductData
     */
    private function setProductForImportFiles(Product $product, array $akeneoProductData): void
    {
        $productFilesData = $this->productTransferAkeneoMapper->mapAkeneoProductDataToProductFilesData($akeneoProductData, $product);
        $this->productFacade->editProductFileAttributes($product, $productFilesData);
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

    /**
     * @param array $akeneoProductData
     * @return bool
     */
    public function checkIsAllParametersExistFromAkeneoData(array $akeneoProductData): bool
    {
        $akeneoProductParameters = $this->productTransferAkeneoMapper->findParametersFromAkeneoData($akeneoProductData);

        foreach ($akeneoProductParameters as $akeneoParameterCode => $parameterValue) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoParameterCode);
            if ($parameter === null) {
                return false;
            }
        }

        return true;
    }
}
