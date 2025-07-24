<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\DataFixtures\Demo\BlogArticleDataFixture;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticleTest extends GraphQlTestCase
{
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

    /**
     * @param string $referenceName
     * @param string $expectedImage
     */
    #[DataProvider('getImagesDataProvider')]
    public function testGetBlogArticleImages(string $referenceName, string $expectedImage): void
    {
        $blogArticle = $this->getReference($referenceName, BlogArticle::class);

        $query = '
            query {
                blogArticle(uuid: "' . $blogArticle->getUuid() . '") {
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
        $this->assertStringEndsWith($expectedImage, $responseData['images'][0]['url']);
    }

    /**
     * @return iterable
     */
    public static function getImagesDataProvider(): iterable
    {
        yield [
            'referenceName' => BlogArticleDataFixture::DEMO_BLOG_ARTICLE_PREFIX . '46',
            'expectedImage' => '600.jpg',
        ];

        yield [
            'referenceName' => BlogArticleDataFixture::DEMO_BLOG_ARTICLE_PREFIX . '47',
            'expectedImage' => '601.jpg',
        ];

        yield [
            'referenceName' => BlogArticleDataFixture::DEMO_BLOG_ARTICLE_PREFIX . '48',
            'expectedImage' => '602.jpg',
        ];
    }

    /**
     * @return array
     */
    private function getExpectedBlogArticleArray(): array
    {
        $locale = $this->getFirstDomainLocale();
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());

        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY, BlogCategory::class);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

        $description = str_replace(['    ', PHP_EOL], '', trim(<<<EOT
            <div class="gjs-text-ckeditor">
                <p>
                    Blog Article Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur.
                </p>
            </div>
            <div class="gjs-text-ckeditor">
                <h2>Heading H2</h2>
                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur. Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus quos doloribus accusantium, aliquam commodi molestiae atque laudantium in dolorem esse error blanditiis, debitis facere id voluptate. Accusantium mollitia placeat consequatur.
                </p>
            </div>
            <div class="gjs-products" data-products="9177759,5965879P,9184449,9176544M,7700768"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="5965879P"></div><div class="gjs-product" data-product="9184449"></div><div class="gjs-product" data-product="9176544M"></div><div class="gjs-product" data-product="7700768"></div></div>
        EOT));
        $description = $this->grapesJsParser->parse($description);

        return [
            'data' => [
                'blogArticle' => [
                    'name' => t('Blog article example %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'uuid' => $this->blogArticle->getUuid(),
                    'text' => $description,
                    'createdAt' => $this->blogArticle->getCreatedAt()->format(DATE_ATOM),
                    'visibleOnHomepage' => true,
                    'publishDate' => $this->blogArticle->getPublishDate()->format(DATE_ATOM),
                    'perex' => t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'seoTitle' => t('title - Blog article example %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'seoMetaDescription' => t('Blog article example %counter% %locale% - Meta description', ['%counter%' => 1, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'seoH1' => t('Blog article example %counter% %locale% - H1', ['%counter%' => 1, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                    'blogCategories' => [
                        ['name' => t('Main blog page - %locale%', ['%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
                    ],
                    'link' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl),
                    'slug' => '/' . $friendlyUrl->getSlug(),
                    'breadcrumb' => [
                        [
                            'name' => $firstBlogCategory->getName($locale),
                            'slug' => $firstBlogCategorySlug,
                        ],
                        [
                            'name' => t('Blog article example %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                            'slug' => '/' . $friendlyUrl->getSlug(),
                        ],
                    ],
                ],
            ],
        ];
    }
}
