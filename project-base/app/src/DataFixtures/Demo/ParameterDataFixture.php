<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterDataFactory;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\ParameterGroup;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;
use App\Model\Product\Parameter\ParameterValueDataFactory;
use App\Model\Product\Unit\Unit;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameter;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory;

class ParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = 'b048837f-32d5-4a11-ab10-f7e77af02c60';

    public const PARAMETER_PREFIX = 'parameter_';
    public const PARAMETER_SLIDER_WARRANTY = 'parameter_slider_warranty';

    public const string PARAM_ACTIVE = 'active';
    public const string PARAM_ACTIVE_PASSIVE = 'active_passive';
    public const string PARAM_ANNUAL_ENERGY_CONSUMPTION = 'annual_energy_consumption';
    public const string PARAM_BLUETOOTH = 'bluetooth';
    public const string PARAM_CAMERA_TYPE = 'camera_type';
    public const string PARAM_CAPACITY = 'capacity';
    public const string PARAM_COLOR = 'color';
    public const string PARAM_COLOR_PRINTING = 'color_printing';
    public const string PARAM_CONNECTORS = 'connectors';
    public const string PARAM_CONSTRUCTION = 'construction';
    public const string PARAM_COVER = 'cover';
    public const string PARAM_DETERMINATION = 'determination';
    public const string PARAM_DIMENSIONS = 'dimensions';
    public const string PARAM_DISPLAY = 'display';
    public const string PARAM_DISPLAY_SIZE = 'display_size';
    public const string PARAM_DISPLAY_TYPE = 'display_type';
    public const string PARAM_EAR_COUPLING = 'ear_coupling';
    public const string PARAM_ELEMENT_ARRANGEMENT = 'element_arrangement';
    public const string PARAM_ENERGY_EFFICIENCY_CLASS = 'energy_efficiency_class';
    public const string PARAM_ENTER = 'enter';
    public const string PARAM_ERGONOMICS = 'ergonomics';
    public const string PARAM_FOLD = 'fold';
    public const string PARAM_FOLD_UP = 'fold_up';
    public const string PARAM_GPS_MODULE = 'gps_module';
    public const string PARAM_GAMING_MOUSE = 'gaming_mouse';
    public const string PARAM_HDMI = 'hdmi';
    public const string PARAM_INTERFACE = 'interface';
    public const string PARAM_LCD = 'lcd';
    public const string PARAM_LOCALIZATION = 'localization';
    public const string PARAM_MAGAZINE_CAPACITY_FOR_BEANS = 'magazine_capacity_for_beans';
    public const string PARAM_MATERIAL = 'material';
    public const string PARAM_MAXIMUM_SIZE = 'maximum_size';
    public const string PARAM_MEDIA_TYPE = 'media_type';
    public const string PARAM_MEMORY_CARD_SUPPORT = 'memory_card_support';
    public const string PARAM_MILK_RESERVOIR_CAPACITY = 'milk_reservoir_capacity';
    public const string PARAM_MULTIMEDIA = 'multimedia';
    public const string PARAM_NFC = 'nfc';
    public const string PARAM_NUMBER_OF_BUTTONS = 'number_of_buttons';
    public const string PARAM_NUMBER_OF_COLORS = 'number_of_colors';
    public const string PARAM_NUMBER_OF_PROCESSOR_CORES = 'number_of_processor_cores';
    public const string PARAM_OVERALL_PERFORMANCE = 'overall_performance';
    public const string PARAM_PAGES_COUNT = 'pages_count';
    public const string PARAM_PARALLEL_PORT = 'parallel_port';
    public const string PARAM_POWER_SUPPLY = 'power_supply';
    public const string PARAM_PRESSURE = 'pressure';
    public const string PARAM_PRINT_RESOLUTION = 'print_resolution';
    public const string PARAM_PRINT_TECHNOLOGY = 'print_technology';
    public const string PARAM_PROCESSOR_FREQUENCY_GHZ = 'processor_frequency_ghz';
    public const string PARAM_RAM = 'ram';
    public const string PARAM_RECORDING_ON = 'recording_on';
    public const string PARAM_RESOLUTION = 'resolution';
    public const string PARAM_RESOLUTION_OF_REAR_CAMERA = 'resolution_of_rear_camera';
    public const string PARAM_SCREEN_SIZE = 'screen_size';
    public const string PARAM_SENSITIVITY_ISO = 'sensitivity_iso';
    public const string PARAM_SUPPORTED_OS = 'supported_os';
    public const string PARAM_SYSTEM_TYPE = 'system_type';
    public const string PARAM_TECHNOLOGY = 'technology';
    public const string PARAM_TUNER = 'tuner';
    public const string PARAM_USB = 'usb';
    public const string PARAM_VIEWFINDER_TYPE = 'viewfinder_type';
    public const string PARAM_WARRANTY_IN_YEARS = 'warranty_in_years';
    public const string PARAM_WATER_RESERVOIR_CAPACITY = 'water_reservoir_capacity';
    public const string PARAM_WEIGHT = 'weight';
    public const string PARAM_WEIGHT_KG = 'weight_kg';
    public const string PARAM_WIFI = 'wifi';

    /**
     * @var array<string, array<string, string|\App\DataFixtures\Demo\ParameterDataFixtureData>>
     */
    private static array $parameterNameCacheByLocale = [];

    /**
     * @param \App\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory $categoryParameterFactory
     */
    public function __construct(
        private readonly ParameterDataFactory $parameterDataFactory,
        private readonly ParameterFacade $parameterFacade,
        private readonly ParameterValueDataFactory $parameterValueDataFactory,
        private readonly ParameterRepository $parameterRepository,
        private readonly EntityManagerDecorator $entityManager,
        private readonly Domain $domain,
        private readonly CategoryParameterFactory $categoryParameterFactory,
    ) {
    }

    /**
     * @param string $referenceName
     * @param string $locale
     * @return string
     */
    public function getParameterNameByReferenceName(string $referenceName, string $locale): string
    {
        $parameterNames = $this->getParameterData($locale);

        if (!array_key_exists($referenceName, $parameterNames)) {
            throw new InvalidArgumentException('Parameter name reference "' . $referenceName . '" not found');
        }

        return $parameterNames[$referenceName] instanceof ParameterDataFixtureData ? $parameterNames[$referenceName]->name : $parameterNames[$referenceName];
    }

    /**
     * @param string $locale
     * @return array<string, string|\App\DataFixtures\Demo\ParameterDataFixtureData>
     */
    private function getParameterData(string $locale): array
    {
        $translationDomain = Translator::DATA_FIXTURES_TRANSLATION_DOMAIN;

        if (isset(self::$parameterNameCacheByLocale[$locale])) {
            return self::$parameterNameCacheByLocale[$locale];
        }

        /** @var \App\Model\Product\Parameter\ParameterGroup $mainInformationParameterGroup */
        $mainInformationParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_MAIN_INFORMATION);
        /** @var \App\Model\Product\Parameter\ParameterGroup $connectionMethodParameterGroup */
        $connectionMethodParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_CONNECTION_METHOD);
        /** @var \App\Model\Product\Parameter\ParameterGroup $mouseParameterGroup */
        $mouseParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_MAIN_INFORMATION_MOUSE);
        /** @var \App\Model\Product\Unit\Unit $unitInch */
        $unitInch = $this->getReference(UnitDataFixture::UNIT_INCH);

        $data = [
            self::PARAM_ACTIVE => t('Active', [], $translationDomain, $locale),
            self::PARAM_ACTIVE_PASSIVE => t('Active/Passive', [], $translationDomain, $locale),
            self::PARAM_ANNUAL_ENERGY_CONSUMPTION => t('Annual energy consumption', [], $translationDomain, $locale),
            self::PARAM_BLUETOOTH => t('Bluetooth', [], $translationDomain, $locale),
            self::PARAM_CAMERA_TYPE => t('Camera type', [], $translationDomain, $locale),
            self::PARAM_CAPACITY => t('Capacity', [], $translationDomain, $locale),
            self::PARAM_COLOR => new ParameterDataFixtureData(
                t('Color', [], $translationDomain, $locale),
                Parameter::PARAMETER_TYPE_COLOR,
                [
                    $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
                    $this->getReference(CategoryDataFixture::CATEGORY_TV),
                ],
                Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
            ),
            self::PARAM_COLOR_PRINTING => t('Color printing', [], $translationDomain, $locale),
            self::PARAM_CONNECTORS => t('Connectors', [], $translationDomain, $locale),
            self::PARAM_CONSTRUCTION => t('Construction', [], $translationDomain, $locale),
            self::PARAM_COVER => t('Cover', [], $translationDomain, $locale),
            self::PARAM_DETERMINATION => t('Determination', [], $translationDomain, $locale),
            self::PARAM_DIMENSIONS => t('Dimensions', [], $translationDomain, $locale),
            self::PARAM_DISPLAY => t('Display', [], $translationDomain, $locale),
            self::PARAM_DISPLAY_SIZE => t('Display Size', [], $translationDomain, $locale),
            self::PARAM_DISPLAY_TYPE => t('Display type', [], $translationDomain, $locale),
            self::PARAM_EAR_COUPLING => t('Ear Coupling', [], $translationDomain, $locale),
            self::PARAM_ELEMENT_ARRANGEMENT => t('Element arrangement', [], $translationDomain, $locale),
            self::PARAM_ENERGY_EFFICIENCY_CLASS => t('Energy efficiency class', [], $translationDomain, $locale),
            self::PARAM_ENTER => t('Enter', [], $translationDomain, $locale),
            self::PARAM_ERGONOMICS => t('Ergonomics', [], $translationDomain, $locale),
            self::PARAM_FOLD => t('Fold', [], $translationDomain, $locale),
            self::PARAM_FOLD_UP => t('Fold-up', [], $translationDomain, $locale),
            self::PARAM_GPS_MODULE => t('GPS module', [], $translationDomain, $locale),
            self::PARAM_GAMING_MOUSE => new ParameterDataFixtureData(
                t('Gaming mouse', [], $translationDomain, $locale),
                parameterGroup: $mouseParameterGroup,
            ),
            self::PARAM_HDMI => new ParameterDataFixtureData(
                t('HDMI', [], $translationDomain, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_INTERFACE => t('Interface', [], $translationDomain, $locale),
            self::PARAM_LCD => t('LCD', [], $translationDomain, $locale),
            self::PARAM_LOCALIZATION => t('Localization', [], $translationDomain, $locale),
            self::PARAM_MAGAZINE_CAPACITY_FOR_BEANS => t('Magazine capacity for beans', [], $translationDomain, $locale),
            self::PARAM_MATERIAL => new ParameterDataFixtureData(
                t('Material', [], $translationDomain, $locale),
                asFilterInCategories: [
                    $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
                    $this->getReference(CategoryDataFixture::CATEGORY_TV),
                ],
                akeneoType: Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
            ),
            self::PARAM_MAXIMUM_SIZE => t('Maximum size', [], $translationDomain, $locale),
            self::PARAM_MEDIA_TYPE => t('Media type', [], $translationDomain, $locale),
            self::PARAM_MEMORY_CARD_SUPPORT => t('Memory card support', [], $translationDomain, $locale),
            self::PARAM_MILK_RESERVOIR_CAPACITY => t('Milk reservoir capacity', [], $translationDomain, $locale),
            self::PARAM_MULTIMEDIA => t('Multimedia', [], $translationDomain, $locale),
            self::PARAM_NFC => t('NFC', [], $translationDomain, $locale),
            self::PARAM_NUMBER_OF_BUTTONS => t('Number of buttons', [], $translationDomain, $locale),
            self::PARAM_NUMBER_OF_COLORS => t('Number of colors', [], $translationDomain, $locale),
            self::PARAM_NUMBER_OF_PROCESSOR_CORES => t('Number of processor cores', [], $translationDomain, $locale),
            self::PARAM_OVERALL_PERFORMANCE => t('Overall performance', [], $translationDomain, $locale),
            self::PARAM_PAGES_COUNT => t('Pages count', [], $translationDomain, $locale),
            self::PARAM_PARALLEL_PORT => t('Parallel port', [], $translationDomain, $locale),
            self::PARAM_POWER_SUPPLY => t('Power supply', [], $translationDomain, $locale),
            self::PARAM_PRESSURE => t('Pressure', [], $translationDomain, $locale),
            self::PARAM_PRINT_RESOLUTION => t('Print resolution', [], $translationDomain, $locale),
            self::PARAM_PRINT_TECHNOLOGY => t('Print technology', [], $translationDomain, $locale),
            self::PARAM_PROCESSOR_FREQUENCY_GHZ => new ParameterDataFixtureData(
                t('Processor frequency (GHz)', [], $translationDomain, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
            ),
            self::PARAM_RAM => t('RAM', [], $translationDomain, $locale),
            self::PARAM_RECORDING_ON => t('Recording on', [], $translationDomain, $locale),
            self::PARAM_RESOLUTION => new ParameterDataFixtureData(
                t('Resolution', [], $translationDomain, $locale),
                parameterGroup: $mainInformationParameterGroup,
            ),
            self::PARAM_RESOLUTION_OF_REAR_CAMERA => t('Resolution of rear camera', [], $translationDomain, $locale),
            self::PARAM_SCREEN_SIZE => new ParameterDataFixtureData(
                t('Screen size', [], $translationDomain, $locale),
                parameterGroup: $mainInformationParameterGroup,
                unit: $unitInch,
            ),
            self::PARAM_SENSITIVITY_ISO => t('Sensitivity (ISO)', [], $translationDomain, $locale),
            self::PARAM_SUPPORTED_OS => t('Supported OS', [], $translationDomain, $locale),
            self::PARAM_SYSTEM_TYPE => t('System type', [], $translationDomain, $locale),
            self::PARAM_TECHNOLOGY => new ParameterDataFixtureData(
                t('Technology', [], $translationDomain, $locale),
                parameterGroup: $mainInformationParameterGroup,
            ),
            self::PARAM_TUNER => t('Tuner', [], $translationDomain, $locale),
            self::PARAM_USB => new ParameterDataFixtureData(
                t('USB', [], $translationDomain, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_VIEWFINDER_TYPE => t('Viewfinder type', [], $translationDomain, $locale),
            self::PARAM_WARRANTY_IN_YEARS => new ParameterDataFixtureData(
                t('Warranty (in years)', [], $translationDomain, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
                [
                    $this->getReference(CategoryDataFixture::CATEGORY_PC),
                ],
            ),
            self::PARAM_WATER_RESERVOIR_CAPACITY => t('Water reservoir capacity', [], $translationDomain, $locale),
            self::PARAM_WEIGHT => t('Weight', [], $translationDomain, $locale),
            self::PARAM_WEIGHT_KG => new ParameterDataFixtureData(
                t('Weight (kg)', [], $translationDomain, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
            ),
            self::PARAM_WIFI => t('WiFi', [], $translationDomain, $locale),
        ];

        self::$parameterNameCacheByLocale[$locale] = $data;

        return self::$parameterNameCacheByLocale[$locale];
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $parameters = $this->getParameterData($firstDomainLocale);

        foreach ($parameters as $parameterDataKey => $parameterDataValue) {
            $parameterNamesByLocale = [];

            foreach ($this->domain->getAllLocales() as $locale) {
                $parameterNamesByLocale[$locale] = $this->getParameterNameByReferenceName($parameterDataKey, $locale);
            }

            if ($parameterDataValue instanceof ParameterDataFixtureData) {
                $parameter = $this->createParameter(
                    $parameterDataKey,
                    $parameterNamesByLocale,
                    $parameterDataValue->asFilterInCategories,
                    $parameterDataValue->parameterType,
                    $parameterDataValue->akeneoType,
                    $parameterDataValue->parameterGroup,
                    $parameterDataValue->unit,
                );
            } else {
                $parameter = $this->createParameter(
                    $parameterDataKey,
                    $parameterNamesByLocale,
                );
            }

            $this->addReference($parameterDataKey, $parameter);
        }

        $parameterColorNamesByLocale = [];
        $parameterMaterialNamesByLocale = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterColorNamesByLocale[$locale] = t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $parameterMaterialNamesByLocale[$locale] = t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->createParameter(
            'color',
            $parameterColorNamesByLocale,
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
            ],
            Parameter::PARAMETER_TYPE_COLOR,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
        );

        $this->createParameter(
            'material',
            $parameterMaterialNamesByLocale,
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
                $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class),
            ],
            null,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
        );

        foreach ($this->domain->getAllLocales() as $locale) {
            $this->findOrCreateParameterValue($locale, t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#ff0000');
            $this->findOrCreateParameterValue($locale, t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#000000');
            $this->findOrCreateParameterValue($locale, t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->findOrCreateParameterValue($locale, t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->findOrCreateParameterValue($locale, t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
        }
    }

    /**
     * @param string $referenceName
     * @param string[] $namesByLocale
     * @param \App\Model\Category\Category[] $asFilterInCategories
     * @param string|null $parameterType
     * @param string|null $akeneoType
     * @param \App\Model\Product\Parameter\ParameterGroup|null $parameterGroup
     * @param \App\Model\Product\Unit\Unit|null $unit
     * @return \App\Model\Product\Parameter\Parameter
     */
    private function createParameter(
        string $referenceName,
        array $namesByLocale,
        array $asFilterInCategories = [],
        ?string $parameterType = null,
        ?string $akeneoType = null,
        ?ParameterGroup $parameterGroup = null,
        ?Unit $unit = null,
    ): Parameter {
        $parameterData = $this->parameterDataFactory->create();
        $parameterData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();
        $parameterData->visible = true;

        if ($parameterType !== null) {
            $parameterData->parameterType = $parameterType;
        }

        $parameterData->akeneoType = $akeneoType;
        $parameterData->name = $namesByLocale;
        $parameterData->group = $parameterGroup;
        $parameterData->unit = $unit;

        $parameter = $this->parameterFacade->findParameterByNames($namesByLocale);

        if ($parameter !== null) {
            $this->parameterFacade->edit($parameter->getId(), $parameterData);
        } else {
            $parameter = $this->parameterFacade->create($parameterData);
        }

        $counter = 0;

        foreach ($asFilterInCategories as $category) {
            // Check if CategoryParameter already exists
            $existingCategoryParameter = $this->entityManager->getRepository(CategoryParameter::class)
                ->findOneBy(['category' => $category, 'parameter' => $parameter]);

            if ($existingCategoryParameter !== null) {
                continue;
            }

            $categoryParameter = $this->categoryParameterFactory->create($category, $parameter, false, $counter);
            $this->entityManager->persist($categoryParameter);
            $counter++;
        }
        $this->entityManager->flush();

        return $parameter;
    }

    /**
     * @param string $locale
     * @param string $text
     * @param string|null $rgbHex
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    private function findOrCreateParameterValue(string $locale, string $text, ?string $rgbHex = null): ParameterValue
    {
        $parameterValueData = $this->parameterValueDataFactory->create();
        $parameterValueData->locale = $locale;
        $parameterValueData->rgbHex = $rgbHex;
        $parameterValueData->text = $text;

        return $this->parameterRepository->findOrCreateParameterValueByParameterValueData(
            $parameterValueData,
        );
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
            ParameterGroupDataFixture::class,
            UnitDataFixture::class,
        ];
    }
}
