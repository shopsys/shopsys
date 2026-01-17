<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Filter;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\BrandFilterChoiceRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class BrandFilterChoiceRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private BrandFilterChoiceRepository $brandFilterChoiceRepository;

    public function testBrandFilterChoicesFromCategoryWithNoBrands(): void
    {
        $brandFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->assertCount(0, $brandFilterChoices);
    }

    public function testBrandFilterChoicesFromCategoryWithBrands(): void
    {
        $brandFilterChoices = $this->getChoicesForCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $this->assertCount(5, $brandFilterChoices);

        $ids = array_map(
            static function (Brand $brand) {
                return $brand->getId();
            },
            $brandFilterChoices,
        );

        $this->assertContains($this->getReference(BrandDataFixture::BRAND_PHILIPS, Brand::class)->getId(), $ids, 'Philips brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_A4TECH, Brand::class)->getId(), $ids, 'A4 brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_LG, Brand::class)->getId(), $ids, 'LG brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_SENCOR, Brand::class)->getId(), $ids, 'Sencor brand should be present in the filter choices');
    }

    public function testGetBrandFilterChoicesForSearchPhone(): void
    {
        $this->skipTestIfFirstDomainIsNotInEnglish();

        $brandFilterChoices = $this->getChoicesForSearchText('phone');

        $this->assertCount(4, $brandFilterChoices);

        $ids = array_map(
            static function (Brand $brand) {
                return $brand->getId();
            },
            $brandFilterChoices,
        );

        $this->assertContains($this->getReference(BrandDataFixture::BRAND_APPLE, Brand::class)->getId(), $ids, 'Apple brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_LG, Brand::class)->getId(), $ids, 'LG brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_SAMSUNG, Brand::class)->getId(), $ids, 'Samsung brand should be present in the filter choices');
        $this->assertContains($this->getReference(BrandDataFixture::BRAND_SONY, Brand::class)->getId(), $ids, 'Sony brand should be present in the filter choices');
    }

    public function testGetBrandFilterChoicesForSearch47(): void
    {
        $brandFilterChoices = $this->getChoicesForSearchText('47');

        $this->assertCount(1, $brandFilterChoices);

        $this->assertSame($this->getReference(BrandDataFixture::BRAND_LG, Brand::class)->getId(), $brandFilterChoices[0]->getId(), 'LG brand should be present in the filter choices');
    }

    /**
     * @return \App\Model\Product\Brand\Brand[]
     */
    protected function getChoicesForCategoryReference(string $categoryReferenceName): array
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $category = $this->getReference($categoryReferenceName, Category::class);
        /** @var \App\Model\Product\Brand\Brand[] $brands */
        $brands = $this->brandFilterChoiceRepository->getBrandFilterChoicesInCategory(
            Domain::FIRST_DOMAIN_ID,
            $pricingGroup,
            $category,
        );

        return $brands;
    }

    /**
     * @return \App\Model\Product\Brand\Brand[]
     */
    protected function getChoicesForSearchText(string $searchText): array
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );
        $domainConfig1 = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        /** @var \App\Model\Product\Brand\Brand[] $brands */
        $brands = $this->brandFilterChoiceRepository->getBrandFilterChoicesForSearch(
            $domainConfig1->getId(),
            $pricingGroup,
            $domainConfig1->getLocale(),
            $searchText,
        );

        return $brands;
    }
}
