<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Article;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
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

    /**
     * @inject
     */
    private ContentDocumentFactory $contentDocumentFactory;

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

    public function testContentIsNormalizedBeforeItIsStored(): void
    {
        $articleData = $this->createArticleData('Article with content');
        $articleData->content = $this->contentDocumentFactory->createFromArray([
            'version' => 1,
            'profile' => 'web',
            'blocks' => [
                ['id' => 'not-a-uuid', 'type' => 'text', 'data' => ['html' => '<p>Hello<script>alert(1)</script></p>', 'unknownKey' => 'dropped']],
                ['id' => '', 'type' => 'removed-block-type', 'data' => []],
            ],
        ]);

        $article = $this->articleFacade->create($articleData);

        $this->em->clear();
        $content = $this->articleFacade->getById($article->getId())->getContentArray();

        $this->assertCount(1, $content['blocks']);
        $block = $content['blocks'][0];
        $this->assertSame('text', $block['type']);
        $this->assertTrue(Uuid::isValid($block['id']));
        $this->assertSame(['html'], array_keys($block['data']));
        $this->assertStringNotContainsString('script', $block['data']['html']);
        $this->assertStringContainsString('Hello', $block['data']['html']);
    }

    public function testEditedContentIsRestoredIntoDataObject(): void
    {
        $article = $this->articleFacade->create($this->createArticleData('Article edited'));
        $this->assertSame([], $this->articleDataFactory->createFromArticle($article)->content->blocks);

        $articleData = $this->articleDataFactory->createFromArticle($article);
        $articleData->content = $this->contentDocumentFactory->createFromArray([
            'blocks' => [['id' => Uuid::uuid4()->toString(), 'type' => 'text', 'data' => ['html' => '<p>Edited</p>']]],
        ]);
        $this->articleFacade->edit($article->getId(), $articleData);

        $this->em->clear();
        $restoredData = $this->articleDataFactory->createFromArticle($this->articleFacade->getById($article->getId()));

        $this->assertCount(1, $restoredData->content->blocks);
        $this->assertSame('<p>Edited</p>', $restoredData->content->blocks[0]->data['html']);
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
