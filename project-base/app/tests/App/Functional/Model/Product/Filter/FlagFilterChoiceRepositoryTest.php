<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Filter\FlagFilterChoiceRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Tests\App\Test\TransactionFunctionalTestCase;

class FlagFilterChoiceRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private FlagFilterChoiceRepository $flagFilterChoiceRepository;

    public function testFlagFilterChoicesFromCategoryWithNoFlags(): void
    {
        $flagFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS);

        $this->assertCount(0, $flagFilterChoices);
    }

    public function testFlagFilterChoicesFromCategoryWithFlags(): void
    {
        $flagFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $this->assertCount(2, $flagFilterChoices);

        $ids = $this->getFlagIds($flagFilterChoices);

        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId(), $ids);
    }

    public function testGetFlagFilterChoicesForSearchPhone(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $flagFilterChoices = $this->getChoicesForSearchText('phone');

        $this->assertCount(3, $flagFilterChoices);

        $ids = $this->getFlagIds($flagFilterChoices);

        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId(), $ids);
        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ, Flag::class)->getId(), $ids);
        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId(), $ids);
    }

    public function testGetFlagFilterChoicesForBook(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $flagFilterChoices = $this->getChoicesForSearchText('book');

        $this->assertCount(2, $flagFilterChoices);

        $ids = $this->getFlagIds($flagFilterChoices);

        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ, Flag::class)->getId(), $ids);
        $this->assertContains($this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class)->getId(), $ids);
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    protected function getChoicesForCategoryReference(string $categoryReferenceName): array
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $category = $this->getReference($categoryReferenceName, Category::class);

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        return $this->flagFilterChoiceRepository->getFlagFilterChoicesInCategory(
            $domainConfig1->getId(),
            $pricingGroup,
            $domainConfig1->getLocale(),
            $category,
        );
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    protected function getChoicesForSearchText(string $searchText): array
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        return $this->flagFilterChoiceRepository->getFlagFilterChoicesForSearch(
            $domainConfig1->getId(),
            $pricingGroup,
            $domainConfig1->getLocale(),
            $searchText,
        );
    }

    /**
     * @param \App\Model\Product\Flag\Flag[] $flagFilterChoices
     * @return int[]
     */
    private function getFlagIds(array $flagFilterChoices): array
    {
        return array_map(
            static function (Flag $flag) {
                return $flag->getId();
            },
            $flagFilterChoices,
        );
    }
}
