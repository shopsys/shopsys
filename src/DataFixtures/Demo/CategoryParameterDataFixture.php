<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\CategoryParameterFacade;
use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Category\CategoryParameterFacade
     */
    private $categoryParameterFacade;

    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    private $parameterRepository;

    /**
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        CategoryParameterFacade $categoryParameterFacade,
        ParameterRepository $parameterRepository
    ) {
        $this->categoryParameterFacade = $categoryParameterFacade;
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $categoryDataFixtureClassReflection = new \ReflectionClass(CategoryDataFixture::class);
        foreach ($categoryDataFixtureClassReflection->getConstants() as $constant) {
            /** @var \App\Model\Category\Category $category */
            $category = $this->getReference($constant);
            $parameters = $this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID);
            $this->categoryParameterFacade->saveRelation($category, $parameters, []);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
