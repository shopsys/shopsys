<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ArticleFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ArticleFacade $articleFacade;

    /**
     * @inject
     */
    private ArticleDataFactory $articleDataFactory;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    public function testCreatedLinkTypeArticleHasNoFriendlyUrl(): void
    {
        $articleData = $this->createArticleData('Link type article');
        $articleData->type = Article::TYPE_LINK;
        $articleData->url = 'https://www.shopsys.com';

        $article = $this->articleFacade->create($articleData);

        $this->assertNull($this->findMainFriendlyUrlSlug($article));
    }

    public function testCreatedSiteTypeArticleHasFriendlyUrl(): void
    {
        $articleData = $this->createArticleData('Site type article');

        $article = $this->articleFacade->create($articleData);

        $this->assertSame('site-type-article', $this->findMainFriendlyUrlSlug($article));
    }

    public function testArticleSwitchedToLinkTypeKeepsItsFriendlyUrl(): void
    {
        $article = $this->articleFacade->create($this->createArticleData('Article switched to link'));

        $articleData = $this->articleDataFactory->createFromArticle($article);
        $articleData->type = Article::TYPE_LINK;
        $articleData->url = 'https://www.shopsys.com';
        $this->articleFacade->edit($article->getId(), $articleData);

        $this->assertSame('article-switched-to-link', $this->findMainFriendlyUrlSlug($article));
    }

    public function testArticleSwitchedBackToSiteTypeGetsFriendlyUrl(): void
    {
        $articleData = $this->createArticleData('Article switched back to site');
        $articleData->type = Article::TYPE_LINK;
        $articleData->url = 'https://www.shopsys.com';
        $article = $this->articleFacade->create($articleData);

        $articleData = $this->articleDataFactory->createFromArticle($article);
        $articleData->type = Article::TYPE_SITE;
        $this->articleFacade->edit($article->getId(), $articleData);

        $this->assertSame('article-switched-back-to-site', $this->findMainFriendlyUrlSlug($article));
    }

    private function createArticleData(string $name): ArticleData
    {
        $articleData = $this->articleDataFactory->create(Domain::FIRST_DOMAIN_ID);
        $articleData->name = $name;
        $articleData->placement = Article::PLACEMENT_FOOTER_1;

        return $articleData;
    }

    private function findMainFriendlyUrlSlug(Article $article): ?string
    {
        return $this->friendlyUrlFacade->findMainFriendlyUrl(
            $article->getDomainId(),
            'front_article_detail',
            $article->getId(),
        )?->getSlug();
    }
}
