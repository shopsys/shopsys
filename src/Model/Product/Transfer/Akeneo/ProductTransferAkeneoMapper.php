<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataException;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Flag\FlagRepository;
use App\Model\Product\Package\ProductPackageDataFactory;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFilesData;
use App\Model\Product\ProductFilesDataFactory;
use App\Model\Transfer\TransferLoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;

class ProductTransferAkeneoMapper
{
    public const PRODUCT_PACKAGE_MINIMAL_INDEX = 1;
    public const PRODUCT_PACKAGE_MAXIMAL_INDEX = 9;
    private const PARAMETER_TEXT_MAX_LENGTH = 100;

    /**
     * @var \App\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \App\Model\Product\ProductFilesDataFactory
     */
    private $productFilesDataFactory;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface
     */
    private $productParameterValueDataFactory;

    /**
     * @var \App\Model\Product\Parameter\ParameterValueDataFactory
     */
    private $parameterValueDataFactory;

    /**
     * @var \App\Model\Product\Flag\FlagRepository
     */
    private $flagRepository;

    /**
     * @var \App\Model\Product\Package\ProductPackageDataFactory
     */
    private $productPackageDataFactory;

    /**
     * @var \App\Model\Product\Transfer\Akeneo\ParameterTransferCachedAkeneoFacade
     */
    private $parameterTransferCachedAkeneoFacade;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFilesDataFactory $productFilesDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Flag\FlagRepository $flagRepository
     * @param \App\Model\Product\Package\ProductPackageDataFactory $productPackageDataFactory
     * @param \App\Model\Product\Transfer\Akeneo\ParameterTransferCachedAkeneoFacade $parameterTransferCachedAkeneoFacade
     */
    public function __construct(
        ProductDataFactory $productDataFactory,
        CategoryFacade $categoryFacade,
        ProductFilesDataFactory $productFilesDataFactory,
        ParameterFacade $parameterFacade,
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory,
        FlagRepository $flagRepository,
        ProductPackageDataFactory $productPackageDataFactory,
        ParameterTransferCachedAkeneoFacade $parameterTransferCachedAkeneoFacade
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->categoryFacade = $categoryFacade;
        $this->productFilesDataFactory = $productFilesDataFactory;
        $this->parameterFacade = $parameterFacade;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->flagRepository = $flagRepository;
        $this->productPackageDataFactory = $productPackageDataFactory;
        $this->parameterTransferCachedAkeneoFacade = $parameterTransferCachedAkeneoFacade;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductFilesData
     */
    public function mapAkeneoProductDataToProductFilesData(array $akeneoProductData, Product $product): ProductFilesData
    {
        $productFilesData = $this->productFilesDataFactory->createFromProduct($product);

        $productFilesData->assemblyInstructionCode = AkeneoProductHelper::mapDomainDataString(
            $productFilesData->assemblyInstructionCode,
            $akeneoProductData['values']['assembly_instruction'] ?? null
        );

        $productFilesData->productTypePlanCode = AkeneoProductHelper::mapDomainDataString(
            $productFilesData->productTypePlanCode,
            $akeneoProductData['values']['product_type_plan'] ?? null
        );

        return $productFilesData;
    }

    /**
     * @param array $akeneoProductData
     * @return string[]
     */
    public function mapAkeneoProductDataToProductSeriesCodeList(array $akeneoProductData): array
    {
        return $akeneoProductData['values']['product_series_entities'][0]['data'] ?? [];
    }

    /**
     * @param array $akeneoProductData
     * @return int|null
     */
    public function mapAkeneoProductDataToParentCatnum(array $akeneoProductData): ?int
    {
        $associationArticleCatnum = $akeneoProductData['values']['association_article'][0]['data'] ?? null;

        return $associationArticleCatnum !== null && $associationArticleCatnum !== 0 ? $associationArticleCatnum : null;
    }

    /**
     * @param array $akeneoProductData
     * @return string|null
     */
    public function mapAkeneoProductDataToDefaultVariantCatnum(array $akeneoProductData): ?string
    {
        $mainVariantCatnum = $akeneoProductData['values']['main_variant_sku'][0]['data'] ?? null;
        if (is_numeric($mainVariantCatnum)) {
            $mainVariantCatnum = (int)$mainVariantCatnum;
        }
        return $mainVariantCatnum !== null ? (string)$mainVariantCatnum : null;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product|null $product
     * @param \App\Model\Transfer\TransferLoggerInterface $transferLogger
     * @return \App\Model\Product\ProductData
     */
    public function mapAkeneoProductDataToProductData(array $akeneoProductData, ?Product $product, TransferLoggerInterface $transferLogger): ProductData
    {
        if ($product === null) {
            $productData = $this->productDataFactory->create();
            $productData->catnum = $akeneoProductData['identifier'];
        } else {
            $productData = $this->productDataFactory->createFromProduct($product);
        }

        $productData->hidden = ($akeneoProductData['enabled'] ?? true) ? false : true;
        $productData->domainHidden = AkeneoProductHelper::mapDomainDataString($productData->domainHidden, $akeneoProductData['values']['domain_hidden'] ?? null);

        $productData->ean = AkeneoProductHelper::mapDataString($akeneoProductData['values']['ean'] ?? null);

        $productData->namePrefix = AkeneoProductHelper::mapLocalizedDataString($productData->namePrefix, $akeneoProductData['values']['name_prefix'] ?? null);
        $productData->name = AkeneoProductHelper::mapLocalizedDataString($productData->name, $akeneoProductData['values']['name'] ?? null);
        $productData->nameSufix = AkeneoProductHelper::mapLocalizedDataString($productData->nameSufix, $akeneoProductData['values']['name_sufix'] ?? null);

        $productData->descriptions = AkeneoProductHelper::mapDomainDataString($productData->descriptions, $akeneoProductData['values']['description'] ?? null);
        $productData->shortDescriptionUsp1 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp1, $akeneoProductData['values']['usp1'] ?? null);
        $productData->shortDescriptionUsp2 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp2, $akeneoProductData['values']['usp2'] ?? null);
        $productData->shortDescriptionUsp3 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp3, $akeneoProductData['values']['usp3'] ?? null);
        $productData->shortDescriptionUsp4 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp4, $akeneoProductData['values']['usp4'] ?? null);
        $productData->shortDescriptionUsp5 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp5, $akeneoProductData['values']['usp5'] ?? null);
        $productData->canBeShippedAsPackage = AkeneoProductHelper::mapDomainDataBool($productData->shortDescriptionUsp5, $akeneoProductData['values']['delivery_method_parcel_allowed'] ?? null, false);

        $productData->domainOrderingPriority = AkeneoProductHelper::mapDomainDataInt($productData->domainOrderingPriority, $akeneoProductData['values']['product_priority'] ?? []);

        $productData->lowPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->lowPriceWithVat, $akeneoProductData['values']['low_price_vat'] ?? null);
        $productData->highPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->highPriceWithVat, $akeneoProductData['values']['high_price_vat'] ?? null);

        $this->fixMandatoryPrices($productData);

        $productCategories = $this->getProductCategories($akeneoProductData['categories']);
        $productData->categoriesByDomainId = [
            Domain::FIRST_DOMAIN_ID => $productCategories,
            Domain::SECOND_DOMAIN_ID => $productCategories,
        ];

        $this->mapProductParameters($akeneoProductData, $productData, $transferLogger);

        $productData->preorder = $akeneoProductData['values']['preorder'][0]['data'] ?? false;

        $vendorDeliveryDate = $akeneoProductData['values']['vendor_delivery_date'][0]['data'] ?? null;
        if ($vendorDeliveryDate !== null) {
            $productData->vendorDeliveryDate = intval($vendorDeliveryDate);
        }

        $productData->flags = AkeneoProductHelper::mapDomainDataArray($productData->flags, $this->getProductFlags($akeneoProductData['values']));

        $this->mapAkeneoProductPackageMainInformationToProductData($akeneoProductData, $productData);

        return $productData;
    }

    /**
     * @param array $akeneoProductData
     * @return string[]
     */
    public function getProductAccessoryCatnumListFromAkeneoProductData(array $akeneoProductData): array
    {
        return $akeneoProductData['associations']['accessories']['products'] ?? [];
    }

    /**
     * @param array $akeneoProductData
     * @return string[]
     */
    public function getMainVariantAccessoryCatnumListFromAkeneoProductData(array $akeneoProductData): array
    {
        return $akeneoProductData['associations']['accessories']['product_models'] ?? [];
    }

    /**
     * @param string[] $akeneoCategoryCodes
     * @return \App\Model\Category\Category[]
     */
    protected function getProductCategories(array $akeneoCategoryCodes): array
    {
        $productCategories = [];

        foreach ($akeneoCategoryCodes as $categoryAkeneoCode) {
            $category = $this->categoryFacade->findByAkeneoCode($categoryAkeneoCode);

            if ($category === null) {
                continue;
            }

            $productCategories[$category->getId()] = $category;

            foreach ($category->getParentsWithoutRootCategory() as $parentCategory) {
                $productCategories[$parentCategory->getId()] = $parentCategory;
            }
        }

        return $productCategories;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Transfer\TransferLoggerInterface $transferLogger
     */
    private function mapProductParameters(array $akeneoProductData, ProductData $productData, TransferLoggerInterface $transferLogger): void
    {
        $akeneoProductParameters = $this->getParametersFromAkeneoData($akeneoProductData);
        $productData->parameters = [];

        foreach ($akeneoProductParameters as $akeneoProductParameterCode => $akeneoProductParameterData) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoProductParameterCode);
            if ($parameter === null) {
                continue;
            }
            try {
                $currentAkeneoProductParameterData = current($akeneoProductParameterData);
                if (array_key_exists('locale', $currentAkeneoProductParameterData) === false || $currentAkeneoProductParameterData['locale'] === null) {
                    $akeneoParameterValueCodes = $this->getParameterValueAkeneoCodes($akeneoProductParameterData, $parameter, $productData->catnum);
                    $this->addParameterValuesByAkeneoValueCodes($parameter, $akeneoParameterValueCodes, $productData);
                } else {
                    $this->addLocalizedParameterValues($akeneoProductParameterData, $parameter, $productData);
                }
            } catch (TransferException $e) {
                $transferLogger->addWarning($e->getMessage());
            }
        }
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string[] $akeneoParameterValueCodes
     * @param \App\Model\Product\ProductData $productData
     */
    private function addParameterValuesByAkeneoValueCodes(Parameter $parameter, array $akeneoParameterValueCodes, ProductData $productData): void
    {
        foreach ($akeneoParameterValueCodes as $akeneoParameterValueCode) {
            foreach (AkeneoHelper::ESHOP_LOCALES_BY_AKENEO_LOCALES as $locale) {
                $productData->parameters[] = $this->createProductParameterValueData(
                    $parameter,
                    $locale,
                    $akeneoParameterValueCode
                );
            }
        }
    }

    /**
     * @param array $akeneoProductParameterData
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string|null $productCatnum
     * @return string[]
     */
    private function getParameterValueAkeneoCodes(array $akeneoProductParameterData, Parameter $parameter, ?string $productCatnum): array
    {
        $currentAkeneoProductParameterData = current($akeneoProductParameterData);
        $currentAkeneoProductParameterDataValue = $currentAkeneoProductParameterData['data'];

        if (is_array($currentAkeneoProductParameterDataValue) === false) {
            return [(string)$currentAkeneoProductParameterDataValue];
        }

        if (array_key_exists('amount', $currentAkeneoProductParameterDataValue)
            && array_key_exists('unit', $currentAkeneoProductParameterDataValue)
        ) {
            $this->checkExpectedParameterUnit(
                $parameter,
                $currentAkeneoProductParameterDataValue['unit'],
                $productCatnum
            );
            return [(string)$currentAkeneoProductParameterDataValue['amount']];
        }

        return array_filter($currentAkeneoProductParameterDataValue, 'is_string');
    }

    /**
     * @param array $akeneoProductParameterData
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\ProductData $productData
     */
    private function addLocalizedParameterValues(
        array $akeneoProductParameterData,
        Parameter $parameter,
        ProductData $productData
    ): void {
        foreach ($akeneoProductParameterData as $currentAkeneoProductParameterData) {
            $locale = AkeneoHelper::findEshopLocaleByAkeneoLocale($currentAkeneoProductParameterData['locale']);
            if ($locale) {
                $productData->parameters[] = $this->createProductParameterValueData(
                    $parameter,
                    $locale,
                    (string)$currentAkeneoProductParameterData['data']
                );
            }
        }
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\ProductData $productData
     */
    private function mapAkeneoProductPackageMainInformationToProductData(array $akeneoProductData, ProductData $productData): void
    {
        $productData->embeddedAccessories = AkeneoProductHelper::mapDomainDataString($productData->embeddedAccessories, $akeneoProductData['values']['embedded_accessories'] ?? null);
        $productData->packageNotIncluded = AkeneoProductHelper::mapDomainDataString($productData->packageNotIncluded, $akeneoProductData['values']['not_included'] ?? null);

        $productData->mountingState = AkeneoProductHelper::mapDataToAllDomains($productData->mountingState, $akeneoProductData['values']['mounting_state'][0]['data'] ?? 'false');
        //todo: ['amount'] + ['unit'] => momentalne opraveno jenom kvuli importu - ale bude asi potreba vsude (entity atd.) predelat
        $productData->packagingUnit = AkeneoProductHelper::mapDataToAllDomains($productData->packagingUnit, $akeneoProductData['values']['packaging_unit'][0]['data']['amount'] ?? null);
        $productData->countPackages = AkeneoProductHelper::mapDataToAllDomains($productData->countPackages, $akeneoProductData['values']['number_package'][0]['data']['amount'] ?? null);
        $productData->totalPackageWeight = AkeneoProductHelper::mapDataToAllDomains($productData->totalPackageWeight, $akeneoProductData['values']['package_weight'][0]['data']['amount'] ?? null);

        foreach ($productData->mountingState as $domainId => $state) {
            $productData->mountingState[$domainId] = AkeneoProductHelper::convertStringToType(str_replace('mounting_state__', '', $state), AkeneoProductHelper::TYPE_BOOLEAN);
            $productData->packagingUnit[$domainId] = AkeneoProductHelper::convertStringToType($productData->packagingUnit[$domainId], AkeneoProductHelper::TYPE_INT);
            $productData->countPackages[$domainId] = AkeneoProductHelper::convertStringToType($productData->countPackages[$domainId], AkeneoProductHelper::TYPE_INT);
            $productData->totalPackageWeight[$domainId] = AkeneoProductHelper::convertStringToType($productData->totalPackageWeight[$domainId], AkeneoProductHelper::TYPE_FLOAT);
        }
    }

    /**
     * @param array $akeneoProductData
     * @return \App\Model\Product\Package\ProductPackageData[]
     */
    public function mapAkeneoProductPackageDetailInformationToProductPackageDataList(array $akeneoProductData): array
    {
        $productPackageDataList = [];
        for ($i = self::PRODUCT_PACKAGE_MINIMAL_INDEX; $i <= self::PRODUCT_PACKAGE_MAXIMAL_INDEX; $i++) {
            $position = $akeneoProductData['values']['package_nr_' . $i][0]['data'] ?? null;
            $length = $akeneoProductData['values']['package_length_' . $i][0]['data']['amount'] ?? null;
            $width = $akeneoProductData['values']['package_width_' . $i][0]['data']['amount'] ?? null;
            $height = $akeneoProductData['values']['package_height_' . $i][0]['data']['amount'] ?? null;
            $weight = $akeneoProductData['values']['package_weight_' . $i][0]['data']['amount'] ?? null;

            if ($position !== null) {
                $productPackageData = $this->productPackageDataFactory->create();
                $productPackageData->position = $position;
                $productPackageData->length = AkeneoProductHelper::convertStringToType($length, AkeneoProductHelper::TYPE_INT);
                $productPackageData->height = AkeneoProductHelper::convertStringToType($height, AkeneoProductHelper::TYPE_INT);
                $productPackageData->width = AkeneoProductHelper::convertStringToType($width, AkeneoProductHelper::TYPE_INT);
                $productPackageData->weight = AkeneoProductHelper::convertStringToType($weight, AkeneoProductHelper::TYPE_FLOAT);
                $productPackageDataList[$position] = $productPackageData;
            }
        }

        return $productPackageDataList;
    }

    /**
     * @param array $akeneoProductDataValues
     * @return array
     */
    protected function getProductFlags(array $akeneoProductDataValues): array
    {
        $selectedFlags = [];
        foreach ($this->flagRepository->getAll() as $flag) {
            if (array_key_exists($flag->getAkeneoCode(), $akeneoProductDataValues)) {
                foreach ($akeneoProductDataValues[$flag->getAkeneoCode()] as $flagData) {
                    if ($flagData['data'] === true) {
                        if ($flagData['locale'] !== null) {
                            $selectedFlags[$flagData['locale']][] = $flag;
                        } else {
                            foreach (array_keys(AkeneoHelper::ESHOP_LOCALES_BY_AKENEO_LOCALES) as $locale) {
                                $selectedFlags[$locale][] = $flag;
                            }
                        }
                    }
                }
            }
        }

        return $selectedFlags;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $parameterValueUnitAkeneoCode
     * @param string $productCatnum
     */
    private function checkExpectedParameterUnit(Parameter $parameter, string $parameterValueUnitAkeneoCode, string $productCatnum): void
    {
        if ($parameter->getParameterUnit() === null || $parameter->getParameterUnit()->getAkeneoCode() !== $parameterValueUnitAkeneoCode
        ) {
            throw new TransferException(
                sprintf(
                    'Product "%s" with parameter "%s" has wrong unit, expected is "%s" but incoming is "%s"',
                    $productCatnum,
                    $parameter->getName('cs'),
                    $parameter->getParameterUnit()->getAkeneoCode(),
                    $parameterValueUnitAkeneoCode
                )
            );
        }
    }

    /**
     * @param array $akeneoProductData
     * @return array
     */
    public function getParametersFromAkeneoData(array $akeneoProductData): array
    {
        $parameters = [];

        foreach ($akeneoProductData['values'] as $key => $data) {
            if (strpos($key, AkeneoImportProductParameterFacade::PREFIX_PARAMETER_CODE) === false) {
                continue;
            }
            $parameters[$key] = $data;
        }

        return $parameters;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $locale
     * @param string $akeneoParameterValueCode
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    private function createProductParameterValueData(
        Parameter $parameter,
        string $locale,
        string $akeneoParameterValueCode
    ): ProductParameterValueData {
        $productParameterValueData = $this->productParameterValueDataFactory->create();

        $parameterTextValue = $this->getParameterValueTextByAkeneoValueCode($parameter, $locale, $akeneoParameterValueCode);

        if (mb_strlen($parameterTextValue) > self::PARAMETER_TEXT_MAX_LENGTH) {
            throw new TransferException(
                sprintf(
                    'Value for parameter "%s" is too long: "%s", expected max %d',
                    $parameter->getAkeneoCode(),
                    $akeneoParameterValueCode,
                    self::PARAMETER_TEXT_MAX_LENGTH
                )
            );
        }

        $parameterValue = $this->parameterFacade->findParameterValueByText($parameterTextValue, $locale);
        if ($parameterValue === null) {
            $parameterValueData = $this->parameterValueDataFactory->create();
            $parameterValueData->text = $parameterTextValue;
            $parameterValueData->locale = $locale;
        } else {
            $parameterValueData = $this->parameterValueDataFactory->createFromParameterValue($parameterValue);
        }

        $productParameterValueData->parameterValueData = $parameterValueData;
        $productParameterValueData->parameter = $parameter;

        return $productParameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $locale
     * @param string $akeneoParameterValueCode
     * @return string
     */
    private function getParameterValueTextByAkeneoValueCode(Parameter $parameter, string $locale, string $akeneoParameterValueCode): string
    {
        if ($parameter->getAkeneoType() === Parameter::AKENEO_ATTRIBUTES_TYPE_BOOLEAN) {
            switch ($akeneoParameterValueCode) {
                case '':
                    return t('No', [], 'messages', $locale);
                case '1':
                    return t('Yes', [], 'messages', $locale);
                default:
                    return $akeneoParameterValueCode;
            }
        }

        if (in_array($parameter->getAkeneoType(), [Parameter::AKENEO_ATTRIBUTES_TYPE_SIMPLE_SELECT, Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT], true)) {
            $valueTextsByLocale = $this->parameterTransferCachedAkeneoFacade->getParameterValueTextsIndexedByLocaleForParameterAndAkeneoValue(
                $parameter->getAkeneoCode(),
                $akeneoParameterValueCode
            );
            if (array_key_exists($locale, $valueTextsByLocale) === false || $valueTextsByLocale[$locale] === null) {
                throw TransferInvalidDataException::createWithViolation(
                    sprintf(
                        'Parameter value `%s` for parameter code `%s` does not have localized `%s` label',
                        $akeneoParameterValueCode,
                        $parameter->getAkeneoCode(),
                        $locale
                    ),
                    ''
                );
            }

            return $valueTextsByLocale[$locale];
        }

        return $akeneoParameterValueCode;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function fixMandatoryPrices(ProductData $productData): void
    {
        foreach ($productData->lowPriceWithVat as $domainId => $lowPriceWithVat) {
            if ($lowPriceWithVat === null) {
                $productData->lowPriceWithVat[$domainId] = Money::zero();
            }
        }
        foreach ($productData->highPriceWithVat as $domainId => $highPriceWithVat) {
            if ($highPriceWithVat === null) {
                $productData->highPriceWithVat[$domainId] = Money::zero();
            }
        }
    }
}
