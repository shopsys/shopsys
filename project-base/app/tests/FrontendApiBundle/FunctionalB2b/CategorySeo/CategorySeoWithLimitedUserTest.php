<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CategorySeo;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\ReadyCategorySeoDataFixture;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

final class CategorySeoWithLimitedUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL;

    /**
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    public function testLimitedUserDoesNotSeePriceBasedSeoCategories(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(
            ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST,
            $this->domain->getId(),
            ReadyCategorySeoMix::class,
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/CategorySeo/graphql/CategorySeoWithLinks.graphql',
            ['urlSlug' => $this->urlGenerator->generate('front_product_list', ['id' => $readyCategorySeoMix->getCategory()->getId()])],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'category');
        $seoMixSlugs = array_column($data['readyCategorySeoMixLinks'], 'slug');

        $expectedSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);

        $this->assertNotContains($expectedSlug, $seoMixSlugs, 'Price-based SEO category should not be visible for limited user');
    }

    public function testLimitedUserCannotAccessPriceBasedSeoCategoryDirectly(): void
    {
        $readyCategorySeoMix = $this->getReferenceForDomain(
            ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_TV_FROM_CHEAPEST,
            $this->domain->getId(),
            ReadyCategorySeoMix::class,
        );

        $urlSlug = $this->urlGenerator->generate('front_category_seo', ['id' => $readyCategorySeoMix->getId()]);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/CategorySeo/graphql/CategorySeo.graphql',
            ['urlSlug' => $urlSlug],
        );

        $this->assertAccessDeniedError($response);
    }
}
