<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository;
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

        $this->assertCount(3, $parameterFilterChoices);

        $ids = array_keys($parameterFilterChoices);

        $this->assertContains(51, $ids);
        $this->assertContains(52, $ids);
        $this->assertContains(28, $ids);

        $parameterParameterValuePair = [
            52 => [$this->getParameterValueIdForFirstDomain('hardcover'), $this->getParameterValueIdForFirstDomain('paper')],
            51 => [$this->getParameterValueIdForFirstDomain('250'), $this->getParameterValueIdForFirstDomain('48'), $this->getParameterValueIdForFirstDomain('50'), $this->getParameterValueIdForFirstDomain('55')],
            28 => [$this->getParameterValueIdForFirstDomain('150 g'), $this->getParameterValueIdForFirstDomain('250 g'), $this->getParameterValueIdForFirstDomain('50 g')],
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
