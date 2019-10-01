<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ParameterFilterChoiceRepositoryTest extends TransactionFunctionalTestCase
{
    public function testParameterFilterChoicesFromCategoryWithNoParameters(): void
    {
        $parameterFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS);

        $this->assertCount(0, $parameterFilterChoices);
    }

    public function testParameterFilterChoicesFromCategory(): void
    {
        $parameterFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->assertCount(3, $parameterFilterChoices);

        $ids = array_map(
            static function (ParameterFilterChoice $parameterFilterChoice) {
                return $parameterFilterChoice->getParameter()->getId();
            },
            $parameterFilterChoices
        );

        $this->assertContains(50, $ids);
        $this->assertContains(51, $ids);
        $this->assertContains(10, $ids);

        $parameterParameterValuePair = [
            51 => [$this->getParameterValueIdForFirstDomain('hardcover'), $this->getParameterValueIdForFirstDomain('paper')],
            50 => [$this->getParameterValueIdForFirstDomain('250'), $this->getParameterValueIdForFirstDomain('48'), $this->getParameterValueIdForFirstDomain('50'), $this->getParameterValueIdForFirstDomain('55')],
            10 => [$this->getParameterValueIdForFirstDomain('150 g'), $this->getParameterValueIdForFirstDomain('250 g'), $this->getParameterValueIdForFirstDomain('50 g')],
        ];

        foreach ($parameterParameterValuePair as $i => $parameterValues) {
            foreach ($parameterValues as $j => $value) {
                $this->assertSame($value, $parameterFilterChoices[$i]->getValues()[$j]->getId());
            }
        }
    }

    /**
     * @param string $categoryReferenceName
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    protected function getChoicesForCategoryReference(string $categoryReferenceName): array
    {
        $repository = $this->getParameterFilterChoiceRepository();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, 1);

        /** @var \Shopsys\ShopBundle\Model\Category\Category $category */
        $category = $this->getReference($categoryReferenceName);

        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        $domainConfig1 = $domain->getDomainConfigById(1);
        return $repository->getParameterFilterChoicesInCategory($domainConfig1->getId(), $pricingGroup, $domainConfig1->getLocale(), $category);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository
     */
    public function getParameterFilterChoiceRepository(): ParameterFilterChoiceRepository
    {
        return $this->getContainer()->get(ParameterFilterChoiceRepository::class);
    }
}
