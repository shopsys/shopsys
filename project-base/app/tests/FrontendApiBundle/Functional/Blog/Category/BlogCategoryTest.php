<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogCategoryTest extends GraphQlTestCase
{
    private const string BLOG_CATEGORY_QUERY_PATH = __DIR__ . '/graphql/BlogCategoryQuery.graphql';

    private const string BLOG_CATEGORY_ARTICLES_QUERY_PATH = __DIR__ . '/graphql/BlogCategoryArticlesQuery.graphql';

    private BlogCategory $blogCategory;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->blogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
    }

    public function testGetBlogCategoryByUuid(): void
    {
        $response = $this->getResponseContentForGql(self::BLOG_CATEGORY_QUERY_PATH, [
            'uuid' => $this->blogCategory->getUuid(),
        ]);

        $this->assertSame(
            $this->getExpectedBlogCategoryArray(),
            $this->getResponseDataForGraphQlType($response, 'blogCategory'),
        );
    }

    public function testGetBlogCategoryByUrlSlug(): void
    {
        $firstSubsectionName = t('First subsection %locale%', ['%locale%' => $this->getFirstDomainLocale()], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $firstSubsectionSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($firstSubsectionName);

        $response = $this->getResponseContentForGql(self::BLOG_CATEGORY_QUERY_PATH, [
            'urlSlug' => $firstSubsectionSlug,
        ]);

        $this->assertSame(
            $this->getExpectedBlogCategoryArray(),
            $this->getResponseDataForGraphQlType($response, 'blogCategory'),
        );
    }

    #[DataProvider('getBlogCategoryArticlesDataProvider')]
    public function testGetBlogCategoryArticles(bool $onlyHomepageArticles): void
    {
        $response = $this->getResponseContentForGql(self::BLOG_CATEGORY_ARTICLES_QUERY_PATH, [
            'uuid' => $this->blogCategory->getUuid(),
            'first' => 3,
            'onlyHomepageArticles' => $onlyHomepageArticles,
        ]);

        $locale = $this->getFirstDomainLocale();

        $this->assertSame([
            'blogArticles' => [
                'edges' => [
                    ['node' => ['name' => t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                    ['node' => ['name' => t('%topic%: how to choose', ['%topic%' => t('Headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                    ['node' => ['name' => t('%topic%: how to choose', ['%topic%' => t('Laptop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)]],
                ],
            ],
        ], $this->getResponseDataForGraphQlType($response, 'blogCategory'));
    }

    public static function getBlogCategoryArticlesDataProvider(): iterable
    {
        yield 'all articles' => ['onlyHomepageArticles' => false];

        yield 'homepage articles only' => ['onlyHomepageArticles' => true];
    }

    public function testGetBlogCategoryReturnsErrorWithWrongUuid(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = sprintf('No visible blog category was found by UUID "%s"', $wrongUuid);

        $response = $this->getResponseContentForGql(self::BLOG_CATEGORY_QUERY_PATH, [
            'uuid' => $wrongUuid,
        ]);
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

        $response = $this->getResponseContentForGql(self::BLOG_CATEGORY_QUERY_PATH, [
            'urlSlug' => $wrongSlug,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    #[DataProvider('getBlogCategoryImageDataProvider')]
    public function testGetBlogCategoryImage(string $referenceName, ?string $expectedImage): void
    {
        $blogCategory = $this->getReference($referenceName, BlogCategory::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BlogCategoryMainImageQuery.graphql', [
            'uuid' => $blogCategory->getUuid(),
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'blogCategory');

        $this->assertArrayHasKey('mainImage', $data);

        if ($expectedImage === null) {
            $this->assertNull($data['mainImage']);
        } else {
            $this->assertStringEndsWith($expectedImage, $data['mainImage']['url']);
        }
    }

    public static function getBlogCategoryImageDataProvider(): iterable
    {
        yield [
            'referenceName' => BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY,
            'expectedImage' => '501.jpg',
        ];

        yield [
            'referenceName' => BlogArticleDataFixture::SECOND_DEMO_BLOG_SUBCATEGORY,
            'expectedImage' => null,
        ];
    }

    private function getExpectedBlogCategoryArray(): array
    {
        $locale = $this->getFirstDomainLocale();
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogcategory_detail', $this->blogCategory->getId());

        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

        return [
            'uuid' => $this->blogCategory->getUuid(),
            'name' => t('First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            'description' => t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            'parent' => [
                'name' => t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
            'children' => [
                ['name' => t('Televisions and displays', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                ['name' => t('Audio and headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ],
            'seo' => [
                'title' => t('title - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'metaDescription' => t('description - First subsection %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'h1' => t('First subsection %locale% - h1', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'metaRobots' => null,
                'canonicalUrl' => null,
            ],
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
                        ], [
                            'name' => t('Product news', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                        ], [
                            'name' => t('Care and maintenance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                        ], [
                            'name' => t('Technology and trends', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                        ],
                    ],
                ],
            ],
        ];
    }
}
