<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class CategoryParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        private readonly CategoryParameterFacade $categoryParameterFacade,
        private readonly ParameterRepository $parameterRepository,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $firstDomainConfig = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig();
        $categoryDataFixtureClassReflection = new ReflectionClass(CategoryDataFixture::class);

        foreach ($categoryDataFixtureClassReflection->getConstants() as $constant) {
            if (!str_starts_with($constant, 'category_')) {
                continue;
            }

            $category = $this->getReference($constant, Category::class);

            if ($category === $categoryElectronics) {
                $parameters = [
                    $this->getReference(ParameterDataFixture::PARAM_COLOR, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_ERGONOMICS, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_GAMING_MOUSE, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_HDMI, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_MATERIAL, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_NUMBER_OF_BUTTONS, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_RESOLUTION, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_SCREEN_SIZE, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_SUPPORTED_OS, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_TECHNOLOGY, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_USB, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_WARRANTY_IN_YEARS, Parameter::class),
                ];
            } else {
                $parameters = $this->parameterRepository->getParametersUsedByProductsInCategory($category, $firstDomainConfig);
            }

            $parametersId = [];

            foreach ($parameters as $parameter) {
                $parametersId[] = $parameter->getId();
            }
            $parametersCollapsed = [];

            if ($category === $categoryElectronics) {
                $parametersCollapsed = [
                    $this->getReference(ParameterDataFixture::PARAM_HDMI, Parameter::class),
                    $this->getReference(ParameterDataFixture::PARAM_SCREEN_SIZE, Parameter::class),
                ];
            }
            $this->categoryParameterFacade->saveRelation($category, $parametersId, $parametersCollapsed);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
