<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Article;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogArticleTest extends GraphQlTestCase
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \App\Model\Blog\Article\BlogArticle
     */
    private $blogArticle;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->friendlyUrlFacade = $this->getContainer()->get(FriendlyUrlFacade::class);
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
                    products {
                        name
                    }
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
                    products {
                        name
                    }
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

        return [
            'data' => [
                'blogArticle' => [
                    'name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], 'dataFixtures', $locale),
                    'uuid' => $this->blogArticle->getUuid(),
                    'text' => t('description - Lorem ipsum dolor sit amet, {products=9177759,7700768,9146508} consectetur {products=9177759,9176508} adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu, laoreet blandit sem. Donec rutrum augue a elit imperdiet, eu vehicula tortor porta. Vivamus pulvinar sem non auctor dictum. Morbi eleifend semper enim, eu faucibus tortor posuere vitae. Donec tincidunt ipsum ullamcorper nisi accumsan tincidunt. Aenean sed velit massa. Nullam interdum eget est ut convallis. Vestibulum et mauris condimentum, rutrum sem congue, suscipit arcu.\nSed tristique vehicula ipsum, ut vulputate tortor feugiat eu. Vivamus convallis quam vulputate faucibus facilisis. Curabitur tincidunt pulvinar leo, eu dapibus augue lacinia a. Fusce sed tincidunt nunc. Morbi a nisi a odio pharetra laoreet nec eget quam. In in nisl tortor. Ut fringilla vitae lectus eu venenatis. Nullam interdum sed odio a posuere. Fusce pellentesque dui vel tortor blandit, a dictum nunc congue.', [], 'dataFixtures', $locale),
                    'createdAt' => $this->blogArticle->getCreatedAt()->format(DATE_ATOM),
                    'visibleOnHomepage' => true,
                    'publishDate' => $this->blogArticle->getPublishDate()->format(DATE_ATOM),
                    'perex' => t('%locale% perex - lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus felis nisi, tincidunt sollicitudin augue eu.', ['%locale%' => $locale], 'dataFixtures', $locale),
                    'seoTitle' => t('title - Ukázkový článek blogu %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], 'dataFixtures', $locale),
                    'seoMetaDescription' => null,
                    'seoH1' => t('Ukázkový článek blogu %counter% %locale% - H1', ['%counter%' => 1, '%locale%' => $locale], 'dataFixtures', $locale),
                    'blogCategories' => [
                        ['name' => t('Hlavní stránka blogu - %locale%', ['%locale%' => $locale], 'dataFixtures', $locale)],
                    ],
                    'link' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl),
                    'slug' => $friendlyUrl->getSlug(),
                    'products' => [],
                    'breadcrumb' => [
                        [
                            'name' => $firstBlogCategory->getName($locale),
                            'slug' => $firstBlogCategorySlug,
                        ],
                        [
                            'name' => t('Ukázkový článek blogu %counter% %locale%', ['%counter%' => 1, '%locale%' => $locale], 'dataFixtures', $locale),
                            'slug' => '/' . $friendlyUrl->getSlug(),
                        ],
                    ],
                ],
            ],
        ];
    }
}
