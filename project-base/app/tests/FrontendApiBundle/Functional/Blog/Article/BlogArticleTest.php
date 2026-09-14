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
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticleTest extends GraphQlTestCase
{
    private const string BLOG_ARTICLE_QUERY_PATH = __DIR__ . '/graphql/BlogArticleQuery.graphql';

    private const string BLOG_ARTICLE_IMAGES_QUERY_PATH = __DIR__ . '/graphql/BlogArticleImagesQuery.graphql';

    private const string BLOG_ARTICLE_AUTHOR_QUERY_PATH = __DIR__ . '/graphql/BlogArticleAuthorQuery.graphql';

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

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
        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_QUERY_PATH, [
            'uuid' => $this->blogArticle->getUuid(),
        ]);

        $this->assertSame(
            $this->getExpectedBlogArticleArray(),
            $this->getResponseDataForGraphQlType($response, 'blogArticle'),
        );
    }

    public function testGetBlogArticleBySlug(): void
    {
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());

        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_QUERY_PATH, [
            'urlSlug' => $friendlyUrl->getSlug(),
        ]);

        $this->assertSame(
            $this->getExpectedBlogArticleArray(),
            $this->getResponseDataForGraphQlType($response, 'blogArticle'),
        );
    }

    public function testGetBlogArticleReturnsErrorWithWrongUuid(): void
    {
        $wrongUuid = '123e4567-e89b-12d3-a456-426614174000';
        $expectedErrorMessage = sprintf('Blog article not found by UUID "%s"', $wrongUuid);

        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_QUERY_PATH, [
            'uuid' => $wrongUuid,
        ]);
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

        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_QUERY_PATH, [
            'urlSlug' => $wrongSlug,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame($expectedErrorMessage, $errors[0]['message']);
    }

    public function testGetBlogArticleImages(): void
    {
        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_IMAGES_QUERY_PATH, [
            'uuid' => $this->blogArticle->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertArrayHasKey('images', $responseData);
        $this->assertCount(1, $responseData['images']);
        $this->assertArrayHasKey('url', $responseData['images'][0]);
        $this->assertStringEndsWith('602.jpg', $responseData['images'][0]['url']);
    }

    public function testGetDemoBlogArticleImage(): void
    {
        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_IMAGES_QUERY_PATH, [
            'uuid' => BlogArticleDataFixture::getDemoBlogArticleUuid(1),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertCount(1, $responseData['images']);
        $this->assertStringEndsWith('610.jpg', $responseData['images'][0]['url']);
    }

    public function testGetBlogArticleAuthor(): void
    {
        $blogArticle = $this->getReference(BlogArticleDataFixture::BLOG_ARTICLE_WITH_AUTHOR, BlogArticle::class);
        $blogArticleAuthor = $blogArticle->getBlogArticleAuthor();
        $locale = $this->getFirstDomainLocale();

        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_AUTHOR_QUERY_PATH, [
            'uuid' => $blogArticle->getUuid(),
        ]);
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

        $response = $this->getResponseContentForGql(self::BLOG_ARTICLE_AUTHOR_QUERY_PATH, [
            'uuid' => $blogArticle->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'blogArticle');

        $this->assertArrayHasKey('author', $responseData);
        $this->assertNull($responseData['author']);
    }

    private function getExpectedBlogArticleArray(): array
    {
        $locale = $this->getFirstDomainLocale();
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());

        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

        $description = $this->grapesJsParser->parse($this->blogArticle->getDescription($locale));
        $seoAttributes = $this->blogArticle->getSeoAttributes(Domain::FIRST_DOMAIN_ID);
        $articleTitle = t('How to choose the right TV for your living room', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $firstBlogSubcategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class);
        $firstBlogSubcategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogSubcategory->getId()]);

        return [
            'name' => $articleTitle,
            'uuid' => $this->blogArticle->getUuid(),
            'text' => $description,
            'createdAt' => $this->blogArticle->getCreatedAt()->format(DATE_ATOM),
            'visibleOnHomepage' => true,
            'publishDate' => $this->blogArticle->getPublishDate(Domain::FIRST_DOMAIN_ID)->format(DATE_ATOM),
            'perex' => $this->blogArticle->getPerex($locale),
            'seo' => [
                'title' => $seoAttributes->getTitle(),
                'metaDescription' => $seoAttributes->getMetaDescription(),
                'h1' => $articleTitle,
                'metaRobots' => null,
                'canonicalUrl' => null,
            ],
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
        ];
    }
}
