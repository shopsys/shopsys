<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MainBlogCategoryDataSettingsTest extends GraphQlTestCase
{
    public function testMainBlogCategoryDataSettings(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/MainBlogCategoryDataSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedBlogUrl = $this->getLocalizedPathOnFirstDomainByRouteName(
            'front_blogcategory_detail',
            [
                'id' => $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class)->getId(),
            ],
        );

        $expectedData = [
            'mainBlogCategoryData' => [
                'mainBlogCategoryUrl' => $expectedBlogUrl,
                'mainBlogCategoryMainImage' => [
                    'name' => 'Main blog page - en',
                    'url' => 'http://127.0.0.1:8000/content-test/images/blogCategory/500.jpg',
                ],
            ],
        ];

        self::assertSame($expectedData, $responseData);
    }
}
