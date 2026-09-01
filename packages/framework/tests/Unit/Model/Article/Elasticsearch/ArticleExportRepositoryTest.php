<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Article\Elasticsearch;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportRepository;

class ArticleExportRepositoryTest extends TestCase
{
    private const int ARTICLE_ID = 1;
    private const string ARTICLE_SLUG = 'article';
    private const array ARTICLE_BREADCRUMB = [['name' => 'Article', 'slug' => 'article']];

    public function testExtractedLinkTypeArticleHasNoUrlData(): void
    {
        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade|\PHPUnit\Framework\MockObject\Stub $friendlyUrlFacadeStub */
        $friendlyUrlFacadeStub = $this->createStub(FriendlyUrlFacade::class);
        $friendlyUrlFacadeStub->method('getMainFriendlyUrl')
            ->willThrowException(new FriendlyUrlNotFoundException());
        $friendlyUrlFacadeStub->method('getAllSlugsByRouteNameAndEntityId')->willReturn([self::ARTICLE_SLUG]);

        $extractedArticle = $this->createArticleExportRepository($friendlyUrlFacadeStub)
            ->extractArticle($this->createArticle(Article::TYPE_LINK));

        $this->assertSame([], $extractedArticle['slug']);
        $this->assertNull($extractedArticle['mainSlug']);
        $this->assertSame([], $extractedArticle['breadcrumb']);
        $this->assertSame('https://www.shopsys.com', $extractedArticle['url']);
    }

    public function testExtractedSiteTypeArticleHasUrlData(): void
    {
        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade|\PHPUnit\Framework\MockObject\Stub $friendlyUrlFacadeStub */
        $friendlyUrlFacadeStub = $this->createStub(FriendlyUrlFacade::class);
        $friendlyUrlFacadeStub->method('getMainFriendlyUrl')->willReturn(
            new FriendlyUrl('front_article_detail', self::ARTICLE_ID, Domain::FIRST_DOMAIN_ID, self::ARTICLE_SLUG),
        );
        $friendlyUrlFacadeStub->method('getAllSlugsByRouteNameAndEntityId')->willReturn([self::ARTICLE_SLUG]);

        $extractedArticle = $this->createArticleExportRepository($friendlyUrlFacadeStub)
            ->extractArticle($this->createArticle(Article::TYPE_SITE));

        $this->assertSame([self::ARTICLE_SLUG], $extractedArticle['slug']);
        $this->assertSame(self::ARTICLE_SLUG, $extractedArticle['mainSlug']);
        $this->assertSame(self::ARTICLE_BREADCRUMB, $extractedArticle['breadcrumb']);
    }

    private function createArticleExportRepository(FriendlyUrlFacade $friendlyUrlFacade): ArticleExportRepository
    {
        /** @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade|\PHPUnit\Framework\MockObject\Stub $breadcrumbFacadeStub */
        $breadcrumbFacadeStub = $this->createStub(BreadcrumbFacade::class);
        $breadcrumbFacadeStub->method('getBreadcrumbOnDomain')->willReturn(self::ARTICLE_BREADCRUMB);

        /** @var \Shopsys\FrameworkBundle\Model\Article\ArticleRepository|\PHPUnit\Framework\MockObject\Stub $articleRepositoryStub */
        $articleRepositoryStub = $this->createStub(ArticleRepository::class);

        return new ArticleExportRepository(
            $articleRepositoryStub,
            $friendlyUrlFacade,
            $breadcrumbFacadeStub,
            new GrapesJsParser(),
        );
    }

    private function createArticle(string $type): Article
    {
        $articleData = new ArticleData();
        $articleData->domainId = Domain::FIRST_DOMAIN_ID;
        $articleData->name = 'Article';
        $articleData->placement = Article::PLACEMENT_FOOTER_1;
        $articleData->type = $type;

        if ($type === Article::TYPE_LINK) {
            $articleData->url = 'https://www.shopsys.com';
        }

        $article = new Article($articleData);
        (new ReflectionClass($article))->getProperty('id')->setValue($article, self::ARTICLE_ID);

        return $article;
    }
}
