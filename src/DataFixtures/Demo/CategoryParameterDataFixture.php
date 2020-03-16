<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\CategoryParameterFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class CategoryParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    private const DEMO_PARAMETERS = [10, 50, 51];

    /**
     * @var \App\Model\Category\CategoryParameterFacade
     */
    private $categoryParameterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     */
    public function __construct(ParameterFacade $parameterFacade, CategoryParameterFacade $categoryParameterFacade)
    {
        $this->parameterFacade = $parameterFacade;
        $this->categoryParameterFacade = $categoryParameterFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $parameters = [];
        foreach (self::DEMO_PARAMETERS as $parameterId) {
            $parameters[] = $this->parameterFacade->getById($parameterId);
        }

        $this->categoryParameterFacade->saveRelation($category, $parameters);
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
