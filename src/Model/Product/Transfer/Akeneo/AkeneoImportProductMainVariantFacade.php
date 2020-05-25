<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\AbstractAkeneoImportTransfer;
use App\Component\Akeneo\Transfer\AkeneoImportTransferDependency;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade;

class AkeneoImportProductMainVariantFacade extends AbstractAkeneoImportTransfer
{
    private const IS_PRODUCT_MAIN_VARIANT = true;

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
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoFacade $productTransferAkeneoFacade
     * @param \App\Model\Product\Transfer\Akeneo\TransferredProductProcessor $transferredProductProcessor
     * @param \App\Model\Product\Transfer\Akeneo\ProductTransferAkeneoMapper $productTransferAkeneoMapper
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     * @param \App\Model\Product\Series\Transfer\Akeneo\AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        AkeneoImportTransferDependency $akeneoImportTransferDependency,
        ProductTransferAkeneoFacade $productTransferAkeneoFacade,
        TransferredProductProcessor $transferredProductProcessor,
        ProductTransferAkeneoMapper $productTransferAkeneoMapper,
        AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
        AkeneoImportProductSeriesFacade $akeneoImportProductSeriesFacade,
        ParameterFacade $parameterFacade,
        ProductDataFactory $productDataFactory,
        ProductFacade $productFacade
    ) {
        parent::__construct($akeneoImportTransferDependency);
        $this->productTransferAkeneoFacade = $productTransferAkeneoFacade;
        $this->transferredProductProcessor = $transferredProductProcessor;
        $this->productTransferAkeneoMapper = $productTransferAkeneoMapper;
        $this->akeneoImportProductParameterFacade = $akeneoImportProductParameterFacade;
        $this->akeneoImportProductGroupParameterFacade = $akeneoImportProductGroupParameterFacade;
        $this->akeneoImportProductSeriesFacade = $akeneoImportProductSeriesFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productDataFactory = $productDataFactory;
        $this->productFacade = $productFacade;
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
     * @param array $akeneoProductModelData
     */
    protected function processItem($akeneoProductModelData): void
    {
        $akeneoProductModelData['identifier'] = $akeneoProductModelData['code'] ?? null;
        $product = $this->transferredProductProcessor->processProduct($akeneoProductModelData, $this->logger, self::IS_PRODUCT_MAIN_VARIANT);

        $this->setParametersForProductMainVariant($product, $akeneoProductModelData);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param array $akeneoProductModelData
     */
    private function setParametersForProductMainVariant(Product $product, array $akeneoProductModelData): void
    {
        $akeneoVariantData = $this->downloadVariantData($akeneoProductModelData);
        $variantAttributeCodes = $this->getVariantAttributeCodesForFirstLevelAxis($akeneoVariantData);
        $variantParameters = [];
        foreach ($variantAttributeCodes as $variantAttributeCode) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($variantAttributeCode);
            if ($parameter === null) {
                continue;
            }

            $variantParameters[] = $parameter;
        }

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->variantParameters = $variantParameters;
        $this->productFacade->edit($product->getId(), $productData);
    }

    /**
     * @param array $akeneoProductModelData
     * @return array
     */
    private function downloadVariantData(array $akeneoProductModelData): array
    {
        $familyCode = $akeneoProductModelData['family'];
        $familyVariantCode = $akeneoProductModelData['family_variant'];

        return  $this->productTransferAkeneoFacade->getFamilyVariant($familyCode, $familyVariantCode);
    }

    /**
     * @param array $akeneoVariantData
     * @return string[]
     */
    private function getVariantAttributeCodesForFirstLevelAxis(array $akeneoVariantData): array
    {
        $variantAttributeCodes = [];
        foreach (($akeneoVariantData['variant_attribute_sets'] ?? []) as $variantAttributeSet) {
            if (($variantAttributeSet['level'] ?? null) === 1) {
                $variantAttributeCodes = $variantAttributeSet['axes'] ?? [];
                break;
            }
        }

        return $variantAttributeCodes;
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
