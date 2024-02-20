<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;

class CategoryParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CategoryParameterFacade $categoryParameterFacade,
        private readonly ParameterRepository $parameterRepository,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $firstDomainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
        $categoryDataFixtureClassReflection = new ReflectionClass(CategoryDataFixture::class);

        foreach ($categoryDataFixtureClassReflection->getConstants() as $constant) {
            if (!str_starts_with($constant, 'category_')) {
                continue;
            }

            $category = $this->getReference($constant, Category::class);
            $parameters = $this->parameterRepository->getParametersUsedByProductsInCategory($category, $firstDomainConfig);
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
