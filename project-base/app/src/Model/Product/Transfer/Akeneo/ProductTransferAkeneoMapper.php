<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Component\Akeneo\Product\AkeneoProductHelper;
use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Flag\FlagRepository;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\ParameterValueDataFactory;
use App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFilesData;
use App\Model\Product\ProductFilesDataFactory;
use App\Model\Transfer\TransferLoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;

class ProductTransferAkeneoMapper
{
    private const PARAMETER_TEXT_MAX_LENGTH = 300;

    private ParameterValueDataFactory $parameterValueDataFactory;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFilesDataFactory $productFilesDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Flag\FlagRepository $flagRepository
     * @param \App\Model\Product\Transfer\Akeneo\ParameterTransferCachedAkeneoFacade $parameterTransferCachedAkeneoFacade
     */
    public function __construct(
        private ProductDataFactory $productDataFactory,
        private CategoryFacade $categoryFacade,
        private ProductFilesDataFactory $productFilesDataFactory,
        private ParameterFacade $parameterFacade,
        private ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory,
        private FlagRepository $flagRepository,
        private ParameterTransferCachedAkeneoFacade $parameterTransferCachedAkeneoFacade,
    ) {
        $this->parameterValueDataFactory = $parameterValueDataFactory;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductFilesData
     */
    public function mapAkeneoProductDataToProductFilesData(
        array $akeneoProductData,
        Product $product,
    ): ProductFilesData {
        $productFilesData = $this->productFilesDataFactory->createFromProduct($product);

        $productFilesData->assemblyInstructionCode = AkeneoProductHelper::mapDomainDataString(
            $productFilesData->assemblyInstructionCode,
            $akeneoProductData['values']['assembly_instruction'] ?? null,
        );

        $productFilesData->productTypePlanCode = AkeneoProductHelper::mapDomainDataString(
            $productFilesData->productTypePlanCode,
            $akeneoProductData['values']['product_type_plan'] ?? null,
        );

        return $productFilesData;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product|null $product
     * @param \App\Model\Transfer\TransferLoggerInterface $transferLogger
     * @return \App\Model\Product\ProductData
     */
    public function mapAkeneoProductDataToProductData(
        array $akeneoProductData,
        ?Product $product,
        TransferLoggerInterface $transferLogger,
    ): ProductData {
        if ($product === null) {
            $productData = $this->productDataFactory->create();
            $productData->catnum = $akeneoProductData['identifier'];
        } else {
            $productData = $this->productDataFactory->createFromProduct($product);
        }

        $productData->hidden = $akeneoProductData['enabled'] ?? true ? false : true;
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

        $productData->domainOrderingPriority = AkeneoProductHelper::mapDomainDataInt($productData->domainOrderingPriority, $akeneoProductData['values']['product_priority'] ?? []);

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
    private function mapProductParameters(
        array $akeneoProductData,
        ProductData $productData,
        TransferLoggerInterface $transferLogger,
    ): void {
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
                $transferLogger->warning($e->getMessage());
            }
        }
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string[] $akeneoParameterValueCodes
     * @param \App\Model\Product\ProductData $productData
     */
    private function addParameterValuesByAkeneoValueCodes(
        Parameter $parameter,
        array $akeneoParameterValueCodes,
        ProductData $productData,
    ): void {
        foreach ($akeneoParameterValueCodes as $akeneoParameterValueCode) {
            foreach (AkeneoHelper::ESHOP_LOCALES_BY_AKENEO_LOCALES as $locale) {
                $productData->parameters[] = $this->createProductParameterValueData(
                    $parameter,
                    $locale,
                    $akeneoParameterValueCode,
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
    private function getParameterValueAkeneoCodes(
        array $akeneoProductParameterData,
        Parameter $parameter,
        ?string $productCatnum,
    ): array {
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
                $productCatnum,
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
        ProductData $productData,
    ): void {
        foreach ($akeneoProductParameterData as $currentAkeneoProductParameterData) {
            $locale = AkeneoHelper::findEshopLocaleByAkeneoLocale($currentAkeneoProductParameterData['locale']);

            if ($locale) {
                $productData->parameters[] = $this->createProductParameterValueData(
                    $parameter,
                    $locale,
                    (string)$currentAkeneoProductParameterData['data'],
                );
            }
        }
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
    private function checkExpectedParameterUnit(
        Parameter $parameter,
        string $parameterValueUnitAkeneoCode,
        string $productCatnum,
    ): void {
        if ($parameter->getUnit() === null || $parameter->getUnit()->getAkeneoCode() !== $parameterValueUnitAkeneoCode
        ) {
            throw new TransferException(
                sprintf(
                    'Product "%s" with parameter "%s" has wrong unit, expected is "%s" but incoming is "%s"',
                    $productCatnum,
                    $parameter->getName('cs'),
                    $parameter->getUnit()->getAkeneoCode(),
                    $parameterValueUnitAkeneoCode,
                ),
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
        string $akeneoParameterValueCode,
    ): ProductParameterValueData {
        $productParameterValueData = $this->productParameterValueDataFactory->create();

        $parameterTextValue = $this->getParameterValueTextByAkeneoValueCode($parameter, $locale, $akeneoParameterValueCode);

        if (mb_strlen($parameterTextValue) > self::PARAMETER_TEXT_MAX_LENGTH) {
            throw new TransferException(
                sprintf(
                    'Value for parameter "%s" is too long: "%s", expected max %d',
                    $parameter->getAkeneoCode(),
                    $akeneoParameterValueCode,
                    self::PARAMETER_TEXT_MAX_LENGTH,
                ),
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
    private function getParameterValueTextByAkeneoValueCode(
        Parameter $parameter,
        string $locale,
        string $akeneoParameterValueCode,
    ): string {
        if ($parameter->getAkeneoType() === Parameter::AKENEO_ATTRIBUTES_TYPE_BOOLEAN) {
            switch ($akeneoParameterValueCode) {
                case '':
                    return t('No', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
                case '1':
                    return t('Yes', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
                default:
                    return $akeneoParameterValueCode;
            }
        }

        if (in_array($parameter->getAkeneoType(), [Parameter::AKENEO_ATTRIBUTES_TYPE_SIMPLE_SELECT, Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT], true)) {
            $valueTextsByLocale = $this->parameterTransferCachedAkeneoFacade->getParameterValueTextsIndexedByLocaleForParameterAndAkeneoValue(
                $parameter->getAkeneoCode(),
                $akeneoParameterValueCode,
            );

            if (array_key_exists($locale, $valueTextsByLocale) === false || $valueTextsByLocale[$locale] === null) {
                throw new TransferException(
                    sprintf(
                        'Parameter value `%s` for parameter code `%s` does not have localized `%s` label',
                        $akeneoParameterValueCode,
                        $parameter->getAkeneoCode(),
                        $locale,
                    ),
                    0,
                );
            }

            return $valueTextsByLocale[$locale];
        }

        return $akeneoParameterValueCode;
    }
}
