<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use App\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogCategoryTest extends GraphQlTestCase
{
    private BlogCategory $blogCategory;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY);
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
                    blogCategoriesTree {
                        name
                        children {
                            name
                        }
                    }
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogCategoryArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryByUrlSlug(): void
    {
        $firstSubsectionName = t('First subsection %locale%', ['%locale%' => $this->getFirstDomainLocale()], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $firstSubsectionSlug = TransformString::stringToFriendlyUrlSlug($firstSubsectionName);

        $query = '
            query {
                blogCategory(urlSlug: "' . $firstSubsectionSlug . '") {
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
                    blogCategoriesTree {
                        name
                        children {
                            name
                        }
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
                            ['node' => ['name' => t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                            ['node' => ['name' => t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                            ['node' => ['name' => t('GrapesJS page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogCategoryArticlesForHomepage(): void
    {
        $uuid = $this->blogCategory->getUuid();
        $query = '
            query {
                blogCategory(uuid: "' . $uuid . '") {
                    blogArticles(first:3, onlyHomepageArticles: true) {
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
                            ['node' => ['name' => t('Blog article for search testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                            ['node' => ['name' => t('Blog article for products testing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                            ['node' => ['name' => t('GrapesJS page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
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
                    'name' => t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'description' => t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'parent' => [
                        'name' => t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    ],
                    'children' => [],
                    'seoTitle' => t('title - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'seoH1' => t('First subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'seoMetaDescription' => t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'link' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl),
                    'slug' => '/' . $friendlyUrl->getSlug(),
                    'breadcrumb' => [
                        [
                            'name' => $firstBlogCategory->getName($locale),
                            'slug' => $firstBlogCategorySlug,
                        ],
                        [
                            'name' => t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            'slug' => $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $this->blogCategory->getId()]),
                        ],
                    ],
                    'blogCategoriesTree' => [
                        [
                            'name' => t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            'children' => [
                                [
                                    'name' => t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                                ], [
                                    'name' => t('Second subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
