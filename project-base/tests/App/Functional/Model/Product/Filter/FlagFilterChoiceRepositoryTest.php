<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class FlagFilterChoiceRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository
     * @inject
     */
    private $flagFilterChoiceRepository;

    public function testFlagFilterChoicesFromCategoryWithNoFlags(): void
    {
        $flagFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS);

        $this->assertCount(0, $flagFilterChoices);
    }

    public function testFlagFilterChoicesFromCategoryWithFlags(): void
    {
        $flagFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $this->assertCount(2, $flagFilterChoices);

        $ids = array_map(
            static function (Flag $flag) {
                return $flag->getId();
            },
            $flagFilterChoices
        );

        $this->assertContains(2, $ids);
        $this->assertContains(3, $ids);
    }

    public function testGetFlagFilterChoicesForSearchPhone(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $flagFilterChoices = $this->getChoicesForSearchText('phone');

        $this->assertCount(3, $flagFilterChoices);

        $ids = array_map(
            static function (Flag $flag) {
                return $flag->getId();
            },
            $flagFilterChoices
        );

        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
        $this->assertContains(3, $ids);
    }

    public function testGetFlagFilterChoicesForBook(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $flagFilterChoices = $this->getChoicesForSearchText('book');

        $this->assertCount(2, $flagFilterChoices);

        $ids = array_map(
            static function (Flag $flag) {
                return $flag->getId();
            },
            $flagFilterChoices
        );

        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
    }

    /**
     * @param string $categoryReferenceName
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getChoicesForCategoryReference(string $categoryReferenceName): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference($categoryReferenceName);

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        return $this->flagFilterChoiceRepository->getFlagFilterChoicesInCategory($domainConfig1->getId(), $pricingGroup, $domainConfig1->getLocale(), $category);
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getChoicesForSearchText(string $searchText): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        return $this->flagFilterChoiceRepository->getFlagFilterChoicesForSearch($domainConfig1->getId(), $pricingGroup, $domainConfig1->getLocale(), $searchText);
    }
}
