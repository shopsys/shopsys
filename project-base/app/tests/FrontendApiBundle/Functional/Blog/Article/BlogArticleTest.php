<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\Component\GrapesJs\GrapesJsParser;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use App\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->blogArticle = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE);
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
     * @return array
     */
    private function getExpectedBlogArticleArray(): array
    {
        $locale = $this->getFirstDomainLocale();
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(1, 'front_blogarticle_detail', $this->blogArticle->getId());

        /** @var \App\Model\Blog\Category\BlogCategory $firstBlogCategory */
        $firstBlogCategory = $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_CATEGORY);
        $firstBlogCategorySlug = $this->urlGenerator->generate('front_blogcategory_detail', ['id' => $firstBlogCategory->getId()]);

        $description = t(
            '<div class="gjs-text-ckeditor">
                    description - Lorem ipsum dolor sit amet,
                </div>
                %productsFirstRow%
                <div class="gjs-text-ckeditor">
                    consectetur
                </div>
                %productsSecondRow%
                <div class="gjs-text-ckeditor">adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu,
                    laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta.
                    Vivamus pulvinar sem non auctor dictum.
                    Morbi eleifend semper enim, eu faucibus tortor posuere vitae.
                    Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt.
                    Aenean sed velit massa. Nullam interdum eget est ut convallis.
                    Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.
                    \nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu.
                    Vivamus convallis quam vulputate faucibus facilisis.
                    Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a.
                    Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam.
                    In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere.
                    Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.
                </div>',
            [
                '%productsFirstRow%' => '<div class="gjs-products" data-products="9177759,7700768,9146508"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="7700768"></div><div class="gjs-product" data-product="9146508"></div></div>',
                '%productsSecondRow%' => '<div class="gjs-products" data-products="9177759,9176508"><div class="gjs-product" data-product="9177759"></div><div class="gjs-product" data-product="9176508"></div></div>',
            ],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $locale,
        );
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
