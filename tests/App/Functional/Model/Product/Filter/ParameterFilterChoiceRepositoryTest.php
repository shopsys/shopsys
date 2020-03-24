<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ParameterFilterChoiceRepositoryTest extends ParameterTransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository
     * @inject
     */
    private $parameterFilterChoiceRepository;

    public function testParameterFilterChoicesFromCategoryWithNoParameters(): void
    {
        $parameterFilterChoices = $this->getParameterValueIdsForCategoryReferenceIndexedByParameterId(CategoryDataFixture::CATEGORY_GARDEN_TOOLS);

        $this->assertCount(0, $parameterFilterChoices);
    }

    public function testParameterFilterChoicesFromCategory(): void
    {
        $parameterFilterChoices = $this->getParameterValueIdsForCategoryReferenceIndexedByParameterId(CategoryDataFixture::CATEGORY_BOOKS);

        $this->assertCount(3, $parameterFilterChoices);

        $ids = array_keys($parameterFilterChoices);

        $this->assertContains(50, $ids);
        $this->assertContains(51, $ids);
        $this->assertContains(10, $ids);

        $parameterParameterValuePair = [
            51 => [$this->getParameterValueIdForFirstDomain('hardcover'), $this->getParameterValueIdForFirstDomain('paper')],
            50 => [$this->getParameterValueIdForFirstDomain('250'), $this->getParameterValueIdForFirstDomain('48'), $this->getParameterValueIdForFirstDomain('50'), $this->getParameterValueIdForFirstDomain('55')],
            10 => [$this->getParameterValueIdForFirstDomain('150 g'), $this->getParameterValueIdForFirstDomain('250 g'), $this->getParameterValueIdForFirstDomain('50 g')],
        ];

        foreach ($parameterParameterValuePair as $parameterId => $parameterValues) {
            foreach ($parameterValues as $parameterValue) {
                $this->assertContains($parameterValue, $parameterFilterChoices[$parameterId]);
            }
        }
    }

    /**
     * @param string $categoryReferenceName
     * @return array
     */
    protected function getParameterValueIdsForCategoryReferenceIndexedByParameterId(string $categoryReferenceName): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference($categoryReferenceName);

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $parameterFilterChoices = $this->parameterFilterChoiceRepository->getParameterFilterChoicesInCategory($domainConfig1->getId(), $pricingGroup, $domainConfig1->getLocale(), $category);

        $parameterValuesByParameterId = [];

        foreach ($parameterFilterChoices as $parameterFilterChoice) {
            $parameterValuesByParameterId[$parameterFilterChoice->getParameter()->getId()] = array_map(
                function ($parameterValue) {
                    return $parameterValue->getId();
                },
                $parameterFilterChoice->getValues()
            );
        }

        return $parameterValuesByParameterId;
    }
}
