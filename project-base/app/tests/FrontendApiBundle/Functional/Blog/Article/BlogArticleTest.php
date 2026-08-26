<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticleTest extends GraphQlTestCase
{
    private const string RENAMED_BLOG_CATEGORY_NAME = 'Renamed ancestor blog category';

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @inject
     */
    private BlogCategoryDataFactory $blogCategoryDataFactory;

    /**
     * @inject
     */
    private BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade;

    private BlogArticle $blogArticle;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    protected GrapesJsParser $grapesJsParser;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->blogArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class);
    }

    public function testGetBlogArticleByUuid(): void
    {
        $uuid = $this->blogArticle->getUuid();
        $query = '
            query {
                blogArticle(uuid: "' . $uuid . '") {
                    name
                    uuid
                    text
                    createdAt
                    visibleOnHomepage    
                    publishDate
                    perex
                    seoTitle
                    seoMetaDescription
                    seoH1
                    blogCategories {
                        name
                    }
                    link
                    slug
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogArticleArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogArticleBySlug(): void
    {
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());
        $slug = $friendlyUrl->getSlug();
        $query = '
            query {
                blogArticle(urlSlug: "' . $slug . '") {
                    name
                    uuid
                    text
                    createdAt
                    visibleOnHomepage    
                    publishDate
                    perex
                    seoTitle
                    seoMetaDescription
                    seoH1
                    blogCategories {
                        name
                    }
                    link
                    slug
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';

        $arrayExpected = $this->getExpectedBlogArticleArray();

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testGetBlogArticleReturnsErrorWithWrongUuid(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = sprintf('Blog article not found by UUID "%s"', $wrongUuid);

        $query = '
            query {
                blogArticle(uuid: "' . $wrongUuid . '") {
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

    public function testGetBlogArticleReturnsErrorWithWrongSlug(): void
    {
        $wrongSlug = 'wrong-slug';
        $expectedErrorMessage = sprintf('Blog article not found by slug "%s"', $wrongSlug);

        $query = '
            query {
                blogArticle(urlSlug: "' . $wrongSlug . '") {
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

    public function testGetBlogArticleImages(): void
    {
        $query = '
            query {
                blogArticle(uuid: "' . $this->blogArticle->getUuid() . '") {
                    name
                    images {
                        url
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertArrayHasKey('images', $responseData);
        $this->assertCount(1, $responseData['images']);
        $this->assertArrayHasKey('url', $responseData['images'][0]);
        $this->assertStringEndsWith('602.jpg', $responseData['images'][0]['url']);
    }

    public function testGetDemoBlogArticleImage(): void
    {
        $query = '
            query {
                blogArticle(uuid: "' . BlogArticleDataFixture::getDemoBlogArticleUuid(1) . '") {
                    images {
                        url
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertCount(1, $responseData['images']);
        $this->assertStringEndsWith('610.jpg', $responseData['images'][0]['url']);
    }

    public function testGetBlogArticleAuthor(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_WITH_AUTHOR, BlogArticle::class);
        $blogArticleAuthor = $blogArticle->getBlogArticleAuthor();
        $locale = $this->getFirstDomainLocale();

        $query = '
            query {
                blogArticle(uuid: "' . $blogArticle->getUuid() . '") {
                    author {
                        name
                        jobTitle
                        description
                        mainImage {
                            url
                        }
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertArrayHasKey('author', $responseData);
        $this->assertNotNull($responseData['author']);
        $this->assertSame($blogArticleAuthor->getName(), $responseData['author']['name']);
        $this->assertSame($blogArticleAuthor->getJobTitle($locale), $responseData['author']['jobTitle']);
        $this->assertSame($blogArticleAuthor->getDescription($locale), $responseData['author']['description']);
        $this->assertArrayHasKey('mainImage', $responseData['author']);

        if ($responseData['author']['mainImage'] !== null) {
            $this->assertNotEmpty($responseData['author']['mainImage']['url']);
        }
    }

    public function testGetBlogArticleWithoutAuthorReturnsNull(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_WITHOUT_AUTHOR, BlogArticle::class);

        $query = '
            query {
                blogArticle(uuid: "' . $blogArticle->getUuid() . '") {
                    author {
                        name
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertArrayHasKey('author', $responseData);
        $this->assertNull($responseData['author']);
    }

    public function testBlogArticleBreadcrumbReflectsRenamedAncestorBlogCategoryWithoutReexport(): void
    {
        $locale = $this->getFirstDomainLocale();
        $rootBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $ancestorBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $televisionsBlogCategory = $this->getReference(BlogArticleDataFixture::BLOG_CATEGORY_TELEVISIONS, BlogCategory::class);
        $screenTechnologiesBlogCategory = $this->getReference(BlogArticleDataFixture::BLOG_CATEGORY_SCREEN_TECHNOLOGIES, BlogCategory::class);

        $blogArticlesData = $this->blogArticleElasticsearchFacade->getByBlogCategory($screenTechnologiesBlogCategory, 0, 1);
        $this->assertNotEmpty($blogArticlesData);
        $blogArticleData = array_shift($blogArticlesData);

        $blogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($ancestorBlogCategory);
        $blogCategoryData->names[$locale] = self::RENAMED_BLOG_CATEGORY_NAME;
        $this->blogCategoryFacade->edit($ancestorBlogCategory->getId(), $blogCategoryData);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/BlogArticleBreadcrumbQuery.graphql',
            ['uuid' => $blogArticleData['uuid']],
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertSame(
            [
                $rootBlogCategory->getName($locale),
                self::RENAMED_BLOG_CATEGORY_NAME,
                $televisionsBlogCategory->getName($locale),
                $screenTechnologiesBlogCategory->getName($locale),
                $blogArticleData['name'],
            ],
            array_column($responseData['breadcrumb'], 'name'),
        );
    }

    private function getExpectedBlogArticleArray(): array
    {
        $locale = $this->getFirstDomainLocale();
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());

        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

        $description = $this->grapesJsParser->parse($this->blogArticle->getDescription($locale));
        $articleTitle = t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $firstBlogSubcategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $firstBlogSubcategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogSubcategory->getId()]);

        return [
            'data' => [
                'blogArticle' => [
                    'name' => $articleTitle,
                    'uuid' => $this->blogArticle->getUuid(),
                    'text' => $description,
                    'createdAt' => $this->blogArticle->getCreatedAt()->format(DATE_ATOM),
                    'visibleOnHomepage' => true,
                    'publishDate' => $this->blogArticle->getPublishDate(Domain::FIRST_DOMAIN_ID)->format(DATE_ATOM),
                    'perex' => $this->blogArticle->getPerex($locale),
                    'seoTitle' => $this->blogArticle->getSeoTitle(Domain::FIRST_DOMAIN_ID),
                    'seoMetaDescription' => $this->blogArticle->getSeoMetaDescription(Domain::FIRST_DOMAIN_ID),
                    'seoH1' => $articleTitle,
                    'blogCategories' => [
                        ['name' => t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                        ['name' => $firstBlogSubcategory->getName($locale)],
                    ],
                    'link' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl),
                    'slug' => '/' . $friendlyUrl->getSlug(),
                    'breadcrumb' => [
                        [
                            'name' => $firstBlogCategory->getName($locale),
                            'slug' => $firstBlogCategorySlug,
                        ],
                        [
                            'name' => $firstBlogSubcategory->getName($locale),
                            'slug' => $firstBlogSubcategorySlug,
                        ],
                        [
                            'name' => $articleTitle,
                            'slug' => '/' . $friendlyUrl->getSlug(),
                        ],
                    ],
                ],
            ],
        ];
    }
}
