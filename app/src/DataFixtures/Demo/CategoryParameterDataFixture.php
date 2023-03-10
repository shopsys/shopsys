<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\CategoryParameterFacade;
use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class CategoryParameterDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryParameterFacade $categoryParameterFacade,
        ParameterRepository $parameterRepository,
        Domain $domain
    ) {
        $this->categoryParameterFacade = $categoryParameterFacade;
        $this->parameterRepository = $parameterRepository;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Category\Category $categoryElectronics */
        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);
        $firstDomainLocale = $this->domain->getDomainConfigById(1)->getLocale();
        $categoryDataFixtureClassReflection = new ReflectionClass(CategoryDataFixture::class);
        foreach ($categoryDataFixtureClassReflection->getConstants() as $constant) {
            /** @var \App\Model\Category\Category $category */
            $category = $this->getReference($constant);
            $parameters = $this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID);
            $parametersId = [];
            foreach ($parameters as $parameter) {
                $parametersId[] = $parameter->getId();
            }
            $parametersCollapsed = [];
            if ($category === $categoryElectronics) {
                $parametersCollapsed = [
                    $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('HDMI', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
                    $this->getReference(ParameterDataFixture::PARAMETER_PREFIX . t('Screen size', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)),
                ];
            }
            $this->categoryParameterFacade->saveRelation($category, $parametersId, $parametersCollapsed);
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
