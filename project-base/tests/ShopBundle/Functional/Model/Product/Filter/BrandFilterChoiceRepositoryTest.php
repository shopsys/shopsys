<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class BrandFilterChoiceRepositoryTest extends TransactionFunctionalTestCase
{
    public function testBrandFilterChoicesFromCategoryWithNoBrands(): void
    {
        $brandFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->assertCount(0, $brandFilterChoices);
    }

    public function testBrandFilterChoicesFromCategoryWithBrands(): void
    {
        $brandFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $this->assertCount(4, $brandFilterChoices);

        $ids = array_map(
            static function (Brand $brand) {
                return $brand->getId();
            },
            $brandFilterChoices
        );

        $this->assertContains(4, $ids);
        $this->assertContains(6, $ids);
        $this->assertContains(3, $ids);
        $this->assertContains(5, $ids);
    }

    public function testGetBrandFilterChoicesForSearchPhone(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $brandFilterChoices = $this->getChoicesForSearchText('phone');

        $this->assertCount(7, $brandFilterChoices);

        $ids = array_map(
            static function (Brand $brand) {
                return $brand->getId();
            },
            $brandFilterChoices
        );

        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
        $this->assertContains(15, $ids);
        $this->assertContains(3, $ids);
        $this->assertContains(4, $ids);
        $this->assertContains(20, $ids);
        $this->assertContains(19, $ids);
    }

    public function testGetBrandFilterChoicesForSearch47(): void
    {
        $brandFilterChoices = $this->getChoicesForSearchText('47');

        $this->assertCount(1, $brandFilterChoices);

        $this->assertSame(3, $brandFilterChoices[0]->getId());
    }

    /**
     * @param string $categoryReferenceName
     * @return \Shopsys\ShopBundle\Model\Product\Brand\Brand[]
     */
    protected function getChoicesForCategoryReference(string $categoryReferenceName): array
    {
        $repository = $this->getBrandFilterChoiceRepository();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, 1);

        /** @var \Shopsys\ShopBundle\Model\Category\Category $category */
        $category = $this->getReference($categoryReferenceName);
        /** @var \Shopsys\ShopBundle\Model\Product\Brand\Brand[] $brands */
        $brands = $repository->getBrandFilterChoicesInCategory(1, $pricingGroup, $category);

        return $brands;
    }

    /**
     * @param string $searchText
     * @return \Shopsys\ShopBundle\Model\Product\Brand\Brand[]
     */
    protected function getChoicesForSearchText(string $searchText): array
    {
        $repository = $this->getBrandFilterChoiceRepository();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        $domainConfig1 = $domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\ShopBundle\Model\Product\Brand\Brand[] $brands */
        $brands = $repository->getBrandFilterChoicesForSearch($domainConfig1->getId(), $pricingGroup, $domainConfig1->getLocale(), $searchText);

        return $brands;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository
     */
    public function getBrandFilterChoiceRepository(): BrandFilterChoiceRepository
    {
        return $this->getContainer()->get(BrandFilterChoiceRepository::class);
    }
}
