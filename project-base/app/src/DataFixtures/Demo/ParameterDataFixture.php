<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\DataObject\ParameterDataFixtureData;
use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameter;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class ParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = 'b048837f-32d5-4a11-ab10-f7e77af02c60';

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
    public const string PARAM_FOLD_UP = 'fold_up';
    public const string PARAM_GPS_MODULE = 'gps_module';
    public const string PARAM_GAMING_MOUSE = 'gaming_mouse';
    public const string PARAM_HDMI = 'hdmi';
    public const string PARAM_HEIGHT = 'height';
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
    public const string PARAM_WIFI = 'wifi';
    public const string PARAM_ZOOM = 'zoom';

    /**
     * @var array<string, array<string, string|\App\DataFixtures\Demo\DataObject\ParameterDataFixtureData>>
     */
    private static array $parameterNameCacheByLocale = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory $categoryParameterFactory
     */
    public function __construct(
        private readonly ParameterDataFactory $parameterDataFactory,
        private readonly ParameterFacade $parameterFacade,
        private readonly EntityManagerDecorator $entityManager,
        private readonly CategoryParameterFactory $categoryParameterFactory,
    ) {
    }

    /**
     * @param string $referenceName
     * @param string $locale
     * @return string
     */
    private function getParameterNameByReferenceName(string $referenceName, string $locale): string
    {
        $parameterNames = $this->getParameterData($locale);

        if (!array_key_exists($referenceName, $parameterNames)) {
            throw new InvalidArgumentException('Parameter name reference "' . $referenceName . '" not found');
        }

        return $parameterNames[$referenceName] instanceof ParameterDataFixtureData ? $parameterNames[$referenceName]->name : $parameterNames[$referenceName];
    }

    /**
     * @param string $locale
     * @return array<string, string|\App\DataFixtures\Demo\DataObject\ParameterDataFixtureData>
     */
    private function getParameterData(string $locale): array
    {
        if (isset(self::$parameterNameCacheByLocale[$locale])) {
            return self::$parameterNameCacheByLocale[$locale];
        }

        $mainInformationParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_MAIN_INFORMATION, ParameterGroup::class);
        $connectionMethodParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_CONNECTION_METHOD, ParameterGroup::class);
        $mouseParameterGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_MAIN_INFORMATION_MOUSE, ParameterGroup::class);
        $propertiesGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_PROPERTIES, ParameterGroup::class);
        $functionsGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_FUNCTIONS, ParameterGroup::class);
        $sizeWeightGroup = $this->getReference(ParameterGroupDataFixture::PARAM_GROUP_FUNCTIONS, ParameterGroup::class);
        $unitInch = $this->getReference(UnitDataFixture::UNIT_INCH, Unit::class);

        $data = [
            self::PARAM_SCREEN_SIZE => new ParameterDataFixtureData(
                t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $mainInformationParameterGroup,
                unit: $unitInch,
            ),
            self::PARAM_TECHNOLOGY => new ParameterDataFixtureData(
                t('Technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $mainInformationParameterGroup,
                orderingPriority: 1,
            ),
            self::PARAM_RESOLUTION => new ParameterDataFixtureData(
                t('Resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $mainInformationParameterGroup,
            ),
            self::PARAM_USB => new ParameterDataFixtureData(
                t('USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_HDMI => new ParameterDataFixtureData(
                t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_GAMING_MOUSE => new ParameterDataFixtureData(
                t('Gaming mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $mouseParameterGroup,
            ),
            self::PARAM_ERGONOMICS => t('Ergonomics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_SUPPORTED_OS => t('Supported OS', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_NUMBER_OF_BUTTONS => t('Number of buttons', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_DIMENSIONS => t('Dimensions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_MEMORY_CARD_SUPPORT => t('Memory card support', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_RAM => t('RAM', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_NUMBER_OF_COLORS => t('Number of colors', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_PROCESSOR_FREQUENCY_GHZ => new ParameterDataFixtureData(
                t('Processor frequency (GHz)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
            ),
            self::PARAM_NUMBER_OF_PROCESSOR_CORES => t('Number of processor cores', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_BLUETOOTH => new ParameterDataFixtureData(
                t('Bluetooth', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_NFC => t('NFC', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GPS_MODULE => t('GPS module', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_DISPLAY => t('Display', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_PARALLEL_PORT => t('Parallel port', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ZOOM => t('Zoom', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_CAMERA_TYPE => t('Camera type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_POWER_SUPPLY => t('Power supply', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_VIEWFINDER_TYPE => t('Viewfinder type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_SENSITIVITY_ISO => t('Sensitivity (ISO)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_HEIGHT => new ParameterDataFixtureData(
                t('Height', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $sizeWeightGroup,
            ),
            self::PARAM_WEIGHT => new ParameterDataFixtureData(
                t('Weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
                parameterGroup: $sizeWeightGroup,
                unit: $this->getReference(UnitDataFixture::UNIT_GRAM, Unit::class),
            ),
            self::PARAM_PRINT_TECHNOLOGY => t('Print technology', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_MAXIMUM_SIZE => t('Maximum size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_LCD => t('LCD', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_PRINT_RESOLUTION => t('Print resolution', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_COLOR_PRINTING => t('Color printing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_WIFI => new ParameterDataFixtureData(
                t('WiFi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $connectionMethodParameterGroup,
            ),
            self::PARAM_MEDIA_TYPE => t('Media type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_CAPACITY => t('Capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_OVERALL_PERFORMANCE => t('Overall performance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_PRESSURE => new ParameterDataFixtureData(
                t('Pressure', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $propertiesGroup,
            ),
            self::PARAM_WATER_RESERVOIR_CAPACITY => new ParameterDataFixtureData(
                t('Water reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $propertiesGroup,
            ),
            self::PARAM_MILK_RESERVOIR_CAPACITY => new ParameterDataFixtureData(
                t('Milk reservoir capacity', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $propertiesGroup,
            ),
            self::PARAM_MAGAZINE_CAPACITY_FOR_BEANS => new ParameterDataFixtureData(
                t('Magazine capacity for beans', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                parameterGroup: $propertiesGroup,
            ),
            self::PARAM_INTERFACE => t('Interface', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_SYSTEM_TYPE => t('System type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ACTIVE_PASSIVE => t('Active/Passive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_LOCALIZATION => t('Localization', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ELEMENT_ARRANGEMENT => t('Element arrangement', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ENTER => t('Enter', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_DISPLAY_SIZE => t('Display size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_DISPLAY_TYPE => t('Display type', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_RESOLUTION_OF_REAR_CAMERA => t('Resolution of rear camera', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_PAGES_COUNT => t('Pages count', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_COVER => t('Cover', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ANNUAL_ENERGY_CONSUMPTION => t('Annual energy consumption', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_ENERGY_EFFICIENCY_CLASS => t('Energy efficiency class', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_TUNER => t('Tuner', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_RECORDING_ON => t('Recording on', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_MULTIMEDIA => t('Multimedia', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_EAR_COUPLING => t('Ear Coupling', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_CONSTRUCTION => t('Construction', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_FOLD_UP => t('Fold-up', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_DETERMINATION => t('Determination', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_CONNECTORS => t('Connectors', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_COLOR => new ParameterDataFixtureData(
                t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                Parameter::PARAMETER_TYPE_COLOR,
                [
                    $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
                    $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class),
                ],
            ),
            self::PARAM_MATERIAL => new ParameterDataFixtureData(
                t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                asFilterInCategories: [
                    $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
                    $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class),
                ],
            ),
            self::PARAM_WARRANTY_IN_YEARS => new ParameterDataFixtureData(
                t('Warranty (in years)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                Parameter::PARAMETER_TYPE_SLIDER,
                [
                    $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class),
                ],
            ),
        ];

        self::$parameterNameCacheByLocale[$locale] = $data;

        return self::$parameterNameCacheByLocale[$locale];
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $firstDomainLocale = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()->getLocale();
        $parameters = $this->getParameterData($firstDomainLocale);

        foreach ($parameters as $parameterDataKey => $parameterDataValue) {
            $parameterNamesByLocale = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $parameterNamesByLocale[$locale] = $this->getParameterNameByReferenceName($parameterDataKey, $locale);
            }

            if ($parameterDataValue instanceof ParameterDataFixtureData) {
                $parameter = $this->createParameter(
                    $parameterDataKey,
                    $parameterNamesByLocale,
                    $parameterDataValue->asFilterInCategories,
                    $parameterDataValue->parameterType,
                    $parameterDataValue->parameterGroup,
                    $parameterDataValue->unit,
                    $parameterDataValue->orderingPriority,
                );
            } else {
                $parameter = $this->createParameter(
                    $parameterDataKey,
                    $parameterNamesByLocale,
                );
            }

            $this->addReference($parameterDataKey, $parameter);
        }
    }

    /**
     * @param string $referenceName
     * @param string[] $namesByLocale
     * @param \App\Model\Category\Category[] $asFilterInCategories
     * @param string|null $parameterType
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null $parameterGroup
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null $unit
     * @param int $orderingPriority
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    private function createParameter(
        string $referenceName,
        array $namesByLocale,
        array $asFilterInCategories = [],
        ?string $parameterType = null,
        ?ParameterGroup $parameterGroup = null,
        ?Unit $unit = null,
        int $orderingPriority = 0,
    ): Parameter {
        $parameterData = $this->parameterDataFactory->create();
        $parameterData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();

        if ($parameterType !== null) {
            $parameterData->parameterType = $parameterType;
        }

        $parameterData->name = $namesByLocale;
        $parameterData->group = $parameterGroup;
        $parameterData->unit = $unit;
        $parameterData->orderingPriority = $orderingPriority;

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
