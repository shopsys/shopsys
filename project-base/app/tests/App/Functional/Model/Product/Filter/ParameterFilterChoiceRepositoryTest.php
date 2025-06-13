<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Tests\App\Test\ParameterTransactionFunctionalTestCase;

class ParameterFilterChoiceRepositoryTest extends ParameterTransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ParameterFilterChoiceRepository $parameterFilterChoiceRepository;

    public function testParameterFilterChoicesFromCategoryWithNoParameters(): void
    {
        $parameterFilterChoices = $this->getParameterValueIdsForCategoryReferenceIndexedByParameterId(
            CategoryDataFixture::CATEGORY_GARDEN_TOOLS,
        );

        $this->assertCount(0, $parameterFilterChoices);
    }

    public function testParameterFilterChoicesFromCategory(): void
    {
        $parameterFilterChoices = $this->getParameterValueIdsForCategoryReferenceIndexedByParameterId(
            CategoryDataFixture::CATEGORY_BOOKS,
        );

        $this->assertCount(4, $parameterFilterChoices);

        $ids = array_keys($parameterFilterChoices);

        $this->assertContains($this->getReference(ParameterDataFixture::PARAM_PAGES_COUNT, Parameter::class)->getId(), $ids);
        $this->assertContains($this->getReference(ParameterDataFixture::PARAM_COVER, Parameter::class)->getId(), $ids);
        $this->assertContains($this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId(), $ids);

        $parameterParameterValuePair = [
            $this->getReference(ParameterDataFixture::PARAM_COVER, Parameter::class)->getId() => [$this->getParameterValueIdForFirstDomain('hardcover'), $this->getParameterValueIdForFirstDomain('paper')],
            $this->getReference(ParameterDataFixture::PARAM_PAGES_COUNT, Parameter::class)->getId() => [
                $this->getParameterValueIdForFirstDomain('250', true),
                $this->getParameterValueIdForFirstDomain('48', true),
                $this->getParameterValueIdForFirstDomain('50', true),
                $this->getParameterValueIdForFirstDomain('55', true),
            ],
            $this->getReference(ParameterDataFixture::PARAM_WEIGHT, Parameter::class)->getId() => [$this->getParameterValueIdForFirstDomain('150', true), $this->getParameterValueIdForFirstDomain('250', true), $this->getParameterValueIdForFirstDomain('50', true)],
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
    protected function getParameterValueIdsForCategoryReferenceIndexedByParameterId(
        string $categoryReferenceName,
    ): array {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $category = $this->getReference($categoryReferenceName, Category::class);

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $parameterFilterChoices = $this->parameterFilterChoiceRepository->getParameterFilterChoicesInCategory(
            $domainConfig1->getId(),
            $pricingGroup,
            $domainConfig1->getLocale(),
            $category,
        );

        $parameterValuesByParameterId = [];

        foreach ($parameterFilterChoices as $parameterFilterChoice) {
            $parameterValuesByParameterId[$parameterFilterChoice->getParameter()->getId()] = array_map(
                function ($parameterValue) {
                    return $parameterValue->getId();
                },
                $parameterFilterChoice->getValues(),
            );
        }

        return $parameterValuesByParameterId;
    }
}
