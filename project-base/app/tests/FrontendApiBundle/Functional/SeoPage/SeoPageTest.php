<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\SeoPage;

use App\DataFixtures\Demo\SeoPageDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SeoPageTest extends GraphQlTestCase
{
    private const GRAPHQL_QUERY_PATH = __DIR__ . '/../_graphql/query/SeoPageQuery.graphql';

    public function testSeoPage(): void
    {
        $pageSlug = SeoPageDataFixture::FIRST_DEMO_SEO_PAGE;

        $response = $this->getResponseContentForGql(self::GRAPHQL_QUERY_PATH, [
            'pageSlug' => $pageSlug,
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'seoPage');

        /** @var \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage */
        $seoPage = $this->getReference($pageSlug);

        $domainId = $this->domain->getId();

        self::assertEquals($seoPage->getSeoTitle($domainId), $data['title']);
        self::assertEquals($seoPage->getSeoMetaDescription($domainId), $data['metaDescription']);
        self::assertEquals($seoPage->getCanonicalUrl($domainId), $data['canonicalUrl']);
        self::assertEquals($seoPage->getSeoOgTitle($domainId), $data['ogTitle']);
        self::assertEquals($seoPage->getSeoOgDescription($domainId), $data['ogDescription']);
    }

    public function testSeoPageNotFound(): void
    {
        $response = $this->getResponseContentForGql(self::GRAPHQL_QUERY_PATH, [
            'pageSlug' => 'non-existent-page-slug',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);

        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('extensions', $errors[0]);

        $extensions = $errors[0]['extensions'];

        $this->assertSame('seo-page-not-found', $extensions['userCode']);
        $this->assertSame(404, $extensions['code']);
    }
}
