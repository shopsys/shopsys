<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Slug;

use App\DataFixtures\Demo\ArticleDataFixture;
use App\DataFixtures\Demo\BlogArticleDataFixture;
use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\SeoPageDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\Model\Article\Article;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SlugWithLeadingSlashTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * Every query that resolves an entity by its URL slug must accept the slug both with and without a leading slash,
     * because the API itself returns slugs with a leading slash (e.g. `/canon`).
     */
    public function testSlugQueriesAcceptSlugWithAndWithoutLeadingSlash(): void
    {
        $bareSlugsByField = [
            'product' => $this->getMainSlug('front_product_detail', $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class)->getId()),
            'brand' => $this->getMainSlug('front_brand_detail', $this->getReference(BrandDataFixture::BRAND_CANON, Brand::class)->getId()),
            'flag' => $this->getMainSlug('front_flag_detail', $this->getReference(FlagDataFixture::FLAG_PRODUCT_MADEIN_DE, Flag::class)->getId()),
            'category' => $this->getMainSlug('front_product_list', $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class)->getId()),
            'store' => $this->getMainSlug('front_stores_detail', $this->getReference(StoreDataFixture::STORE_PREFIX . '1', Store::class)->getId()),
            'article' => $this->getMainSlug('front_article_detail', $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, Domain::FIRST_DOMAIN_ID, Article::class)->getId()),
            'blogArticle' => $this->getMainSlug('front_blogarticle_detail', $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_ARTICLE, BlogArticle::class)->getId()),
            'blogCategory' => $this->getMainSlug('front_blogcategory_detail', $this->getReference(BlogArticleDataFixture::FIRST_DEMO_BLOG_SUBCATEGORY, BlogCategory::class)->getId()),
        ];

        foreach ($bareSlugsByField as $field => $bareSlug) {
            $nameWithoutLeadingSlash = $this->getEntityNameByUrlSlug($field, $bareSlug);
            $nameWithLeadingSlash = $this->getEntityNameByUrlSlug($field, '/' . $bareSlug);

            $this->assertSame(
                $nameWithoutLeadingSlash,
                $nameWithLeadingSlash,
                sprintf('Query "%s" must resolve the same entity for slug with and without a leading slash.', $field),
            );
        }
    }

    public function testSeoPageQueryAcceptsSlugWithAndWithoutLeadingSlash(): void
    {
        $pageSlug = SeoPageDataFixture::FIRST_DEMO_SEO_PAGE;

        $titleWithoutLeadingSlash = $this->getSeoPageTitleByPageSlug($pageSlug);
        $titleWithLeadingSlash = $this->getSeoPageTitleByPageSlug('/' . $pageSlug);

        $this->assertSame($titleWithoutLeadingSlash, $titleWithLeadingSlash);
    }

    private function getMainSlug(string $routeName, int $entityId): string
    {
        return $this->friendlyUrlFacade->getMainFriendlyUrlSlug(Domain::FIRST_DOMAIN_ID, $routeName, $entityId);
    }

    private function getEntityNameByUrlSlug(string $field, string $urlSlug): string
    {
        $response = $this->getResponseContentForQuery(sprintf('{ %s(urlSlug: "%s") { name } }', $field, $urlSlug));
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $field);

        return $this->getResponseDataForGraphQlType($response, $field)['name'];
    }

    private function getSeoPageTitleByPageSlug(string $pageSlug): string
    {
        $response = $this->getResponseContentForQuery(sprintf('{ seoPage(pageSlug: "%s") { title } }', $pageSlug));
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'seoPage');

        return $this->getResponseDataForGraphQlType($response, 'seoPage')['title'];
    }
}
