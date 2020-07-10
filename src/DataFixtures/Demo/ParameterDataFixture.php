<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\CategoryParameter;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterDataFactory;
use App\Model\Product\Parameter\ParameterFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Parameter\ParameterValue;
use App\Model\Product\Parameter\ParameterValueDataFactory;
use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory;

class ParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Product\Parameter\ParameterDataFactory
     */
    private ParameterDataFactory $parameterDataFactory;

    /**
     * @var \App\Model\Product\Parameter\ParameterFacade
     */
    private ParameterFacade $parameterFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterValueDataFactory
     */
    private ParameterValueDataFactory $parameterValueDataFactory;

    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    private ParameterRepository $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory
     */
    private ProductParameterValueFactory $productParameterValueFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private EntityManagerDecorator $entityManager;

    /**
     * @param \App\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactory $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     */
    public function __construct(
        ParameterDataFactory $parameterDataFactory,
        ParameterFacade $parameterFacade,
        ParameterValueDataFactory $parameterValueDataFactory,
        ParameterRepository $parameterRepository,
        ProductParameterValueFactory $productParameterValueFactory,
        EntityManagerDecorator $entityManager
    ) {
        $this->parameterDataFactory = $parameterDataFactory;
        $this->parameterFacade = $parameterFacade;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->parameterRepository = $parameterRepository;
        $this->productParameterValueFactory = $productParameterValueFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $parameterColor = $this->createParameter(
            [
                'cs' => 'Barva',
                'sk' => 'Farba',
            ],
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
                $this->getReference(CategoryDataFixture::CATEGORY_TV),
            ],
            Parameter::PARAMETER_TYPE_COLOR,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT
        );
        $parameterMaterial = $this->createParameter(
            [
                'cs' => 'Materiál',
                'sk' => 'Materiál',
            ],
            [
                $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
                $this->getReference(CategoryDataFixture::CATEGORY_TV),
            ],
            null,
            Parameter::AKENEO_ATTRIBUTES_TYPE_MULTI_SELECT
        );

        $parameterValueRedCs = $this->getParameterValue('cs', 'červená', '#ff0000');
        $parameterValueRedSk = $this->getParameterValue('sk', 'červená', '#ff0000');
        $parameterValueBlackCs = $this->getParameterValue('cs', 'černá', '#000000');
        $parameterValueBlackSk = $this->getParameterValue('sk', 'čierná', '#000000');
        $parameterValueWhiteCs = $this->getParameterValue('cs', 'bílá', '#ffffff');

        $parameterValueMetalCs = $this->getParameterValue('cs', 'kov');
        $parameterValueMetalSk = $this->getParameterValue('sk', 'kov');
        $parameterValueWoodCs = $this->getParameterValue('cs', 'dřevo');
        $parameterValueWoodSk = $this->getParameterValue('sk', 'drevo');
        $parameterValuePlasticCs = $this->getParameterValue('cs', 'plast');

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedCs);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedSk);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueBlackCs);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueBlackSk);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueMetalCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueMetalSk);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueWoodCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueWoodSk);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValuePlasticCs);

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2');
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueBlackCs);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueBlackSk);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueWhiteCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueMetalCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueMetalSk);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValuePlasticCs);

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3');
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedCs);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedSk);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueWhiteCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValuePlasticCs);

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4');
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedCs);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueRedSk);
        $this->addParameterValueToProduct($product1, $parameterColor, $parameterValueWhiteCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueWoodCs);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValueWoodSk);
        $this->addParameterValueToProduct($product1, $parameterMaterial, $parameterValuePlasticCs);
    }

    /**
     * @param string[] $namesByLocale
     * @param \App\Model\Category\Category[] $asFilterInCategories
     * @param string|null $parameterType
     * @param string|null $akeneoType
     * @return \App\Model\Product\Parameter\Parameter
     */
    private function createParameter(
        array $namesByLocale,
        array $asFilterInCategories,
        ?string $parameterType,
        ?string $akeneoType
    ): Parameter {
        $parameterData = $this->parameterDataFactory->create();
        $parameterData->visible = true;
        if ($parameterType !== null) {
            $parameterData->parameterType = $parameterType;
        }
        $parameterData->akeneoType = $akeneoType;
        $parameterData->name = $namesByLocale;

        $parameter = $this->parameterFacade->create($parameterData);

        foreach ($asFilterInCategories as $category) {
            $categoryParameter = new CategoryParameter($category, $parameter, false);
            $this->entityManager->persist($categoryParameter);
            $this->entityManager->flush($categoryParameter);
        }

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
            $parameterValueData
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    private function addParameterValueToProduct(Product $product, Parameter $parameter, ParameterValue $parameterValue): void
    {
        $productParameterValue = $this->productParameterValueFactory->create(
            $product,
            $parameter,
            $parameterValue
        );

        $this->entityManager->persist($productParameterValue);
        $this->entityManager->flush($productParameterValue);
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
