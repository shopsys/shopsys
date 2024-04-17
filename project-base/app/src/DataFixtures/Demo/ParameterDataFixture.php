<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterDataFactory;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;
use App\Model\Product\Parameter\ParameterValueDataFactory;
use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory;

class ParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = 'b048837f-32d5-4a11-ab10-f7e77af02c60';

    public const PARAMETER_PREFIX = 'parameter_';
    public const PARAMETER_SLIDER_WARRANTY = 'parameter_slider_warranty';

    /**
     * @param \App\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFactory $categoryParameterFactory
     */
    public function __construct(
        private readonly ParameterDataFactory $parameterDataFactory,
        private readonly ParameterFacade $parameterFacade,
        private readonly ParameterValueDataFactory $parameterValueDataFactory,
        private readonly ParameterRepository $parameterRepository,
        private readonly ProductParameterValueFactory $productParameterValueFactory,
        private readonly EntityManagerDecorator $entityManager,
        private readonly Domain $domain,
        private readonly CategoryParameterFactory $categoryParameterFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $parameterColorNamesByLocale = [];
        $parameterMaterialNamesByLocale = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterColorNamesByLocale[$locale] = t('Color', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $parameterMaterialNamesByLocale[$locale] = t('Material', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $parameterColor = $this->createParameter(
            'color',
            $parameterColorNamesByLocale,
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
            ],
            Parameter::PARAMETER_TYPE_COLOR,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
        );
        $parameterMaterial = $this->createParameter(
            'material',
            $parameterMaterialNamesByLocale,
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class),
                $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class),
            ],
            null,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT,
        );

        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterValueRed = $this->getParameterValue($locale, t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#ff0000');
            $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRed);
            $parameterValueMetal = $this->getParameterValue($locale, t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueMetal);
        }

        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterValueBlack = $this->getParameterValue($locale, t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#000000');
            $this->addParameterValueToProduct($product2, $parameterColor, $parameterValueBlack);
            $parameterValueMetal = $this->getParameterValue($locale, t('metal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterValueToProduct($product2, $parameterMaterial, $parameterValueMetal);
        }

        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3', Product::class);

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterValueRed = $this->getParameterValue($locale, t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#ff0000');
            $this->addParameterValueToProduct($product3, $parameterColor, $parameterValueRed);
            $parameterValuePlastic = $this->getParameterValue($locale, t('plastic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterValueToProduct($product3, $parameterMaterial, $parameterValuePlastic);
        }

        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4', Product::class);

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterValueRed = $this->getParameterValue($locale, t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale), '#ff0000');
            $this->addParameterValueToProduct($product4, $parameterColor, $parameterValueRed);
            $parameterValueWood = $this->getParameterValue($locale, t('wood', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale));
            $this->addParameterValueToProduct($product4, $parameterMaterial, $parameterValueWood);
        }

        $this->createSliderParameterWithValuesAndAssignThemToProducts();
        $this->makeSomeExistingParametersSlider();
    }

    /**
     * @param string $referenceName
     * @param string[] $namesByLocale
     * @param \App\Model\Category\Category[] $asFilterInCategories
     * @param string|null $parameterType
     * @param string|null $akeneoType
     * @return \App\Model\Product\Parameter\Parameter
     */
    private function createParameter(
        string $referenceName,
        array $namesByLocale,
        array $asFilterInCategories,
        ?string $parameterType,
        ?string $akeneoType,
    ): Parameter {
        $parameterData = $this->parameterDataFactory->create();
        $parameterData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();
        $parameterData->visible = true;

        if ($parameterType !== null) {
            $parameterData->parameterType = $parameterType;
        }

        $parameterData->akeneoType = $akeneoType;
        $parameterData->name = $namesByLocale;

        $parameter = $this->parameterFacade->findParameterByNames($namesByLocale);

        if ($parameter !== null) {
            $this->parameterFacade->edit($parameter->getId(), $parameterData);
        } else {
            $parameter = $this->parameterFacade->create($parameterData);
        }

        $counter = 0;

        foreach ($asFilterInCategories as $category) {
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
    private function getParameterValue(string $locale, string $text, ?string $rgbHex = null): ParameterValue
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
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    private function addParameterValueToProduct(
        Product $product,
        Parameter $parameter,
        ParameterValue $parameterValue,
    ): void {
        $productParameterValue = $this->productParameterValueFactory->create(
            $product,
            $parameter,
            $parameterValue,
        );

        $this->entityManager->persist($productParameterValue);
        $this->entityManager->flush();
    }

    private function createSliderParameterWithValuesAndAssignThemToProducts(): void
    {
        $parameterNamesByLocale = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterNamesByLocale[$locale] = t('Warranty (in years)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $parameter = $this->createParameter('warranty', $parameterNamesByLocale, [$this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class)], Parameter::PARAMETER_TYPE_SLIDER, null);
        $this->addReference(self::PARAMETER_SLIDER_WARRANTY, $parameter);

        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 4, Product::class);
        $product9 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 9, Product::class);
        $product11 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 11, Product::class);
        $product16 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 16, Product::class);
        $product18 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 18, Product::class);
        $product35 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 35, Product::class);
        $product52 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 52, Product::class);

        foreach ($this->domain->getAllLocales() as $locale) {
            $this->addParameterValueToProduct($product4, $parameter, $this->getParameterValue($locale, '1'));
            $this->addParameterValueToProduct($product11, $parameter, $this->getParameterValue($locale, '2'));
            $this->addParameterValueToProduct($product16, $parameter, $this->getParameterValue($locale, '3'));
            $this->addParameterValueToProduct($product18, $parameter, $this->getParameterValue($locale, '4'));
            $this->addParameterValueToProduct($product52, $parameter, $this->getParameterValue($locale, '5'));
            $this->addParameterValueToProduct($product9, $parameter, $this->getParameterValue($locale, '4'));
            $this->addParameterValueToProduct($product35, $parameter, $this->getParameterValue($locale, '4'));
        }
    }

    public function makeSomeExistingParametersSlider(): void
    {
        $parameters = [
            // Processor frequency (GHz)
            $this->parameterFacade->getById(15),
            // Weight (kg)
            $this->parameterFacade->getById(10),
        ];

        foreach ($parameters as $parameter) {
            $parameterData = $this->parameterDataFactory->createFromParameter($parameter);
            $parameterData->parameterType = Parameter::PARAMETER_TYPE_SLIDER;

            $this->parameterFacade->edit($parameter->getId(), $parameterData);
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
