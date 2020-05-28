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
use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;

class ProductTransferAkeneoMapper
{
    public const PRODUCT_PACKAGE_MINIMAL_INDEX = 1;
    public const PRODUCT_PACKAGE_MAXIMAL_INDEX = 9;

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
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

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
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\ProductFilesDataFactory $productFilesDataFactory
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface $productParameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Flag\FlagRepository $flagRepository
     * @param \App\Model\Product\Package\ProductPackageDataFactory $productPackageDataFactory
     */
    public function __construct(
        ProductDataFactory $productDataFactory,
        CategoryFacade $categoryFacade,
        ProductFilesDataFactory $productFilesDataFactory,
        ProductTypeFacade $productTypeFacade,
        ParameterFacade $parameterFacade,
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory,
        FlagRepository $flagRepository,
        ProductPackageDataFactory $productPackageDataFactory
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->categoryFacade = $categoryFacade;
        $this->productFilesDataFactory = $productFilesDataFactory;
        $this->productTypeFacade = $productTypeFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->flagRepository = $flagRepository;
        $this->productPackageDataFactory = $productPackageDataFactory;
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
     * @return string|null
     */
    public function mapAkeneoProductDataToParentSkuList(array $akeneoProductData): ?string
    {
        $tmp = $akeneoProductData['values']['association_article'][0]['data'] ?? null;

        return $tmp !== null ? strval($tmp) : null;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Product\ProductData
     */
    public function mapAkeneoProductDataToProductData(array $akeneoProductData, ?Product $product): ProductData
    {
        if ($product === null) {
            $productData = $this->productDataFactory->create();
            $productData->catnum = $akeneoProductData['identifier'];
        } else {
            $productData = $this->productDataFactory->createFromProduct($product);
        }

        $productData->hidden = ($akeneoProductData['enabled'] ?? true) ? false : true;

        $productData->ean = AkeneoProductHelper::mapDataString($akeneoProductData['values']['ean'] ?? null);
        $productData->productType = $this->getProductType($productData->productType, $akeneoProductData);

        $productData->namePrefix = AkeneoProductHelper::mapLocalizedDataString($productData->namePrefix, $akeneoProductData['values']['name_prefix'] ?? null);
        $productData->name = AkeneoProductHelper::mapLocalizedDataString($productData->name, $akeneoProductData['values']['name'] ?? null);
        $productData->nameSufix = AkeneoProductHelper::mapLocalizedDataString($productData->nameSufix, $akeneoProductData['values']['name_sufix'] ?? null);

        $productData->descriptions = AkeneoProductHelper::mapDomainDataString($productData->descriptions, $akeneoProductData['values']['description'] ?? null);
        $productData->shortDescriptionUsp1 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp1, $akeneoProductData['values']['usp1'] ?? null);
        $productData->shortDescriptionUsp2 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp2, $akeneoProductData['values']['usp2'] ?? null);
        $productData->shortDescriptionUsp3 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp3, $akeneoProductData['values']['usp3'] ?? null);
        $productData->shortDescriptionUsp4 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp4, $akeneoProductData['values']['usp4'] ?? null);
        $productData->shortDescriptionUsp5 = AkeneoProductHelper::mapDomainDataString($productData->shortDescriptionUsp5, $akeneoProductData['values']['usp5'] ?? null);

        $productData->lowPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->lowPriceWithVat, $akeneoProductData['values']['low_price_vat'] ?? null);
        $productData->highPriceWithVat = AkeneoProductHelper::mapDomainDataPrices($productData->highPriceWithVat, $akeneoProductData['values']['high_price_vat'] ?? null);

        if (($akeneoProductData['values']['high_price_vat'] ?? null) === null) {
            foreach ($productData->productType as $domainId => $productType) {
                $productData->lowPriceWithVat[$domainId] = Money::zero();
                $productData->highPriceWithVat[$domainId] = Money::zero();
            }
        }

        $productCategories = $this->getProductCategories($akeneoProductData['categories']);
        $productData->categoriesByDomainId = [
            Domain::FIRST_DOMAIN_ID => $productCategories,
            Domain::SECOND_DOMAIN_ID => $productCategories,
        ];

        $this->mapProductParameters($akeneoProductData, $productData);

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
     * @param \App\Model\Product\Type\ProductType[]|null[] $productTypesByDomainId
     * @param array $akeneoProductData
     * @return \App\Model\Product\Type\ProductType[]
     */
    private function getProductType(array $productTypesByDomainId, array $akeneoProductData): array
    {
        $productTypeAkeneoCodesByDomainId = [];
        foreach ($productTypesByDomainId as $domainId => $productType) {
            if ($productType === null) {
                $productTypeAkeneoCodesByDomainId[$domainId] = null;
            } else {
                $productTypeAkeneoCodesByDomainId[$domainId] = $productType->getAkeneoCode();
            }
        }

        $akeneoCodesByDomainId = AkeneoProductHelper::mapDomainDataString(
            $productTypeAkeneoCodesByDomainId,
            $akeneoProductData['values']['product_type']
        );
        foreach ($akeneoCodesByDomainId as $domainId => $akeneoCode) {
            if ($akeneoCode === null) {
                throw TransferInvalidDataException::createWithViolation(
                    sprintf('ProductType for domain `%s` is required', $domainId),
                    'product_type'
                );
            }

            $productType = $this->productTypeFacade->findByAkeneoCode($akeneoCode);
            if ($productType === null) {
                throw TransferInvalidDataException::createWithViolation(
                    sprintf('ProductType with Akeneo code `%s` wasn\'t found.', $akeneoCode),
                    'product_type'
                );
            }

            $productTypesByDomainId[$domainId] = $productType;
        }

        return $productTypesByDomainId;
    }

    /**
     * @param array $akeneoProductData
     * @param \App\Model\Product\ProductData $productData
     */
    private function mapProductParameters(array $akeneoProductData, ProductData $productData): void
    {
        $akeneoProductParameters = $this->getParametersFromAkeneoData($akeneoProductData);
        $productData->parameters = [];

        foreach ($akeneoProductParameters as $akeneoProductParameterCode => $akeneoProductParameterData) {
            $parameter = $this->parameterFacade->findParameterByAkeneoCode($akeneoProductParameterCode);
            if ($parameter === null) {
                continue;
            }

            if (count($akeneoProductParameterData) === 1) {
                $currentAkeneoProductParameterData = current($akeneoProductParameterData);
                if (is_array($currentAkeneoProductParameterData['data'])) {
                    $parameterValueText = (string)$currentAkeneoProductParameterData['data']['amount'];
                    $parameterValueUnit = $currentAkeneoProductParameterData['data']['unit'];

                    $this->checkExpectedParameterUnit($parameter, $parameterValueUnit, $productData->catnum);
                } else {
                    $parameterValueText = (string)$currentAkeneoProductParameterData['data'];
                    $parameterValueUnit = null;
                }

                foreach (AkeneoHelper::AKENEO_LOCALES_MAP_ESHOP_LOCALES as $locale) {
                    $productData->parameters[] = $this->createProductParameterValueData(
                        $parameter,
                        $locale,
                        $parameterValueText,
                        $parameterValueUnit
                    );
                }
            } else {
                foreach ($akeneoProductParameterData as $currentAkeneoProductParameterData) {
                    $locale = AkeneoHelper::findEshopLocaleByAkeneoLocale($currentAkeneoProductParameterData['locale']);
                    if ($locale) {
                        $productData->parameters[] = $this->createProductParameterValueData(
                            $parameter,
                            $locale,
                            (string)$currentAkeneoProductParameterData['data'],
                            null
                        );
                    }
                }
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
        $productData->packagingUnit = AkeneoProductHelper::mapDataToAllDomains($productData->packagingUnit, $akeneoProductData['values']['packaging_unit'][0]['data'] ?? null);
        $productData->countPackages = AkeneoProductHelper::mapDataToAllDomains($productData->countPackages, $akeneoProductData['values']['number_package'][0]['data'] ?? null);
        $productData->totalPackageWeight = AkeneoProductHelper::mapDataToAllDomains($productData->totalPackageWeight, $akeneoProductData['values']['package_weight'][0]['data']['amount'] ?? null);

        foreach ($productData->mountingState as $domainId => $state) {
            $productData->mountingState[$domainId] = AkeneoProductHelper::convertStingToType(str_replace('mounting_state__', '', $state), AkeneoProductHelper::TYPE_BOOLEAN);
            $productData->packagingUnit[$domainId] = AkeneoProductHelper::convertStingToType($productData->packagingUnit[$domainId], AkeneoProductHelper::TYPE_INT);
            $productData->countPackages[$domainId] = AkeneoProductHelper::convertStingToType($productData->countPackages[$domainId], AkeneoProductHelper::TYPE_INT);
            $productData->totalPackageWeight[$domainId] = AkeneoProductHelper::convertStingToType($productData->totalPackageWeight[$domainId], AkeneoProductHelper::TYPE_FLOAT);
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
                $productPackageData->position = AkeneoProductHelper::convertStingToType($position, AkeneoProductHelper::TYPE_INT);
                $productPackageData->length = AkeneoProductHelper::convertStingToType($length, AkeneoProductHelper::TYPE_INT);
                $productPackageData->height = AkeneoProductHelper::convertStingToType($height, AkeneoProductHelper::TYPE_INT);
                $productPackageData->width = AkeneoProductHelper::convertStingToType($width, AkeneoProductHelper::TYPE_INT);
                $productPackageData->weight = AkeneoProductHelper::convertStingToType($weight, AkeneoProductHelper::TYPE_FLOAT);
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
                            foreach (array_keys(AkeneoHelper::AKENEO_LOCALES_MAP_ESHOP_LOCALES) as $locale) {
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
     * @param string $parameterValueUnit
     * @param string $catnum
     */
    private function checkExpectedParameterUnit(Parameter $parameter, string $parameterValueUnit, string $catnum): void
    {
        if ($parameter->getParameterUnit()->getUnit() !== null
            && $parameter->getParameterUnit()->getUnit() !== $parameterValueUnit
        ) {
            throw new TransferException(
                sprintf(
                    'Product "%s" with parameter "%s" has wrong unit, expected is "%s" but incoming is "%s"',
                    $catnum,
                    $parameter->getName('cs'),
                    $parameter->getParameterUnit()->getUnit(),
                    $parameterValueUnit
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
     * @param string $parameterValueText
     * @param string|null $parameterValueUnit
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    private function createProductParameterValueData(Parameter $parameter, string $locale, string $parameterValueText, ?string $parameterValueUnit): ProductParameterValueData
    {
        $productParameterValueData = $this->productParameterValueDataFactory->create();
        $parameterValueData = $this->parameterValueDataFactory->create();

        $parameterValueData->text = $this->prepareParameterValueTextToHumanSpeech($parameter, $locale, $parameterValueText);
        $parameterValueData->unit = $parameterValueUnit;
        $parameterValueData->locale = $locale;

        $productParameterValueData->parameterValueData = $parameterValueData;
        $productParameterValueData->parameter = $parameter;

        return $productParameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $locale
     * @param string $parameterValueText
     * @return string
     */
    private function prepareParameterValueTextToHumanSpeech(Parameter $parameter, string $locale, string $parameterValueText): string
    {
        if ($parameter->getAkeneoType() === Parameter::AKENEO_ATTRIBUTES_TYPE_BOOLEAN) {
            switch ($parameterValueText) {
                case '':
                    return t('No', [], null, $locale);
                case '1':
                    return t('Yes', [], null, $locale);
                default:
                    return $parameterValueText;
            }
        }

        return $parameterValueText;
    }
}
