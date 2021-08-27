<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogCategoryTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Blog\Category\BlogCategory
     */
    private $blogCategory;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $this->blogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY);
        $this->friendlyUrlFacade = $this->getContainer()->get(FriendlyUrlFacade::class);

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
                    link
                    slug
                    breadcrumb {
                        name
                        slug
                    }
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
                    link
                    slug
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogCategoryArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryArticles(): void
    {
        $uuid = $this->blogCategory->getUuid();
        $query = '
            query {
                blogCategory(uuid: "' . $uuid . '") {
                    blogArticles(first:3) {
                        edges {
                            node {
                              name
                            }
                        }
                    }
                }
            }
        ';

        $locale = $this->getFirstDomainLocale();
        $arrayExpected = [
            'data' => [
                'blogCategory' => [
                    'blogArticles' => [
                        'edges' => [
                            ['node' => ['name' => t('Blog article for search testing', [], 'dataFixtures', $locale)]],
                            ['node' => ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 16, '%locale%' => $locale], 'dataFixtures', $locale)]],
                            ['node' => ['name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 17, '%locale%' => $locale], 'dataFixtures', $locale)]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryReturnsErrorWithWrongUuid(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = sprintf('No visible blog category was found by UUID "%s"', $wrongUuid);

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

    public function testGetBlogCategoryReturnsErrorWithWrongSlug(): void
    {
        $wrongSlug = 'wrong-slug';
        $expectedErrorMessage = sprintf('No visible blog category was found by slug "%s"', $wrongSlug);

        $query = '
            query {
                blogCategory(urlSlug: "' . $wrongSlug . '") {
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
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogcategory_detail', $this->blogCategory->getId());

        /** @var \App\Model\Blog\Category\BlogCategory $firstBlogCategory */
        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

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
                    'link' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl),
                    'slug' => '/' . $friendlyUrl->getSlug(),
                    'breadcrumb' => [
                        [
                            'name' => $firstBlogCategory->getName($locale),
                            'slug' => $firstBlogCategorySlug,
                        ],
                        [
                            'name' => t('První podsekce %locale%', ['%locale%' => $locale], 'dataFixtures', $locale),
                            'slug' => $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $this->blogCategory->getId()]),
                        ],
                    ],
                ],
            ],
        ];
    }
}
