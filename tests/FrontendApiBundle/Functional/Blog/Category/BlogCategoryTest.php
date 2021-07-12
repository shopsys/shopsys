<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogCategoryTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Blog\Category\BlogCategory
     */
    private $blogCategory;

    protected function setUp(): void
    {
        $this->blogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY);

        parent::setUp();
    }

    public function testGetBlogCategoryByUuid(): void
    {
        $uuid = $this->blogCategory->getUuid();
        $query = '
            query {
                blogCategory(uuid: "' . $uuid . '") {
                    uuid
                    name
                    description
                    parent {
                        name
                    }
                    children {
                        name
                    }
                    seoTitle
                    seoH1
                    seoMetaDescription
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogCategoryArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryByUrlSlug(): void
    {
        $query = '
            query {
                blogCategory(urlSlug: "prvni-podsekce-cs") {
                    uuid
                    name
                    description
                    parent {
                        name
                    }
                    children {
                        name
                    }
                    seoTitle
                    seoH1
                    seoMetaDescription
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogCategoryArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryReturnsError(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = sprintf('Blog category with UUID "%s" does not exist.', $wrongUuid);

        $query = '
            query {
                blogCategory(uuid: "' . $wrongUuid . '") {
                    name
                }
            }
        ';
        $response = $this->getResponseContentForQuery($query);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    /**
     * @return array
     */
    private function getExpectedBlogCategoryArray(): array
    {
        $locale = $this->getFirstDomainLocale();

        return [
            'data' => [
                'blogCategory' => [
                    'uuid' => $this->blogCategory->getUuid(),
                    'name' => t('První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale),
                    'description' => t('description - První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale),
                    'parent' => [
                        'name' => t('Hlavní stránka blogu - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale),
                    ],
                    'children' => [],
                    'seoTitle' => t('title - První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale),
                    'seoH1' => t('První podsekce %locale% - h1', ['%locale%' => $locale], 'dataFixtures', $locale),
                    'seoMetaDescription' => null,
                ],
            ],
        ];
    }
}
