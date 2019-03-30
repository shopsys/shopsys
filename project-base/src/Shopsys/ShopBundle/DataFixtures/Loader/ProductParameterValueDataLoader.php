<?php

namespace Shopsys\ShopBundle\DataFixtures\Loader;

use Doctrine\Common\Collections\ArrayCollection;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory;
use Shopsys\ShopBundle\DataFixtures\Demo\ParameterDataFixture;

class ProductParameterValueDataLoader
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory
     */
    private $productParameterValueDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory
     */
    private $parameterValueDataFactory;

    /**
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     */
    public function __construct(
        Generator $faker,
        PersistentReferenceFacade $persistentReferenceFacade,
        ProductParameterValueDataFactory $productParameterValueDataFactory,
        ParameterValueDataFactory $parameterValueDataFactory
    ) {
        $this->faker = $faker;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
    }

    /**
     * @param int $fakerSeedNumber
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[]
     */
    public function getParameterValueDataParametersForFakerSeed(int $fakerSeedNumber)
    {
        $this->faker->seed($fakerSeedNumber);
        $productParametersValueData = new ArrayCollection();

        $colorParameter = $this->persistentReferenceFacade->getReference(ParameterDataFixture::REFERENCE_PREFIX . 'color');
        $randomColorValue = $this->faker->randomElement(ParameterDataFixture::PARAMETER_COLORS);

        $firstProductParameterValueData = $this->productParameterValueDataFactory->create();
        $firstProductParameterValueData->parameter = $colorParameter;
        $firstProductParameterValueData->parameterValueData = $this->parameterValueDataFactory->create();
        $firstProductParameterValueData->parameterValueData->text = $randomColorValue;
        $firstProductParameterValueData->parameterValueData->locale = 'en';
        $productParametersValueData->add($firstProductParameterValueData);

        $secondProductParameterValueData = $this->productParameterValueDataFactory->create();
        $secondProductParameterValueData->parameter = $colorParameter;
        $secondProductParameterValueData->parameterValueData = $this->parameterValueDataFactory->create();
        $secondProductParameterValueData->parameterValueData->text = ParameterDataFixture::CZECH_LOCALE_PARAMETER_TRANSLATIONS_BY_ENGLISH_LOCALE[$randomColorValue];
        $secondProductParameterValueData->parameterValueData->locale = 'cs';
        $productParametersValueData->add($secondProductParameterValueData);

        $weightParameter = $this->persistentReferenceFacade->getReference(ParameterDataFixture::REFERENCE_PREFIX . 'weight');
        $weight = $this->faker->randomDigitNotNull;

        $thirdProductParameterValueData = $this->productParameterValueDataFactory->create();
        $thirdProductParameterValueData->parameterValueData = $this->parameterValueDataFactory->create();
        $thirdProductParameterValueData->parameter = $weightParameter;
        $thirdProductParameterValueData->parameterValueData->text = $weight . ' kg';
        $thirdProductParameterValueData->parameterValueData->locale = 'en';
        $productParametersValueData->add($thirdProductParameterValueData);

        $fourthProductParameterValueData = $this->productParameterValueDataFactory->create();
        $fourthProductParameterValueData->parameter = $weightParameter;
        $fourthProductParameterValueData->parameterValueData = $this->parameterValueDataFactory->create();
        $fourthProductParameterValueData->parameterValueData->text = $weight . ' lb';
        $fourthProductParameterValueData->parameterValueData->locale = 'cs';
        $productParametersValueData->add($fourthProductParameterValueData);

        return $productParametersValueData->toArray();
    }
}
