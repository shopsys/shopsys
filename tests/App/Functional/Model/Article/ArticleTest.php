<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Article;

use App\Model\Article\Article;
use DateTime;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ArticleTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface
     * @inject
     */
    private $articleDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface
     * @inject
     */
    private $articleFactory;

    public function testArticleIsCorrectlyRestoredFromDatabase()
    {
        /** @var \App\Model\Article\ArticleData $articleData */
        $articleData = $this->articleDataFactory->create();

        $articleData->name = 'Demonstrative name';
        $articleData->placement = 'topMenu';
        $articleData->seoTitle = 'Demonstrative seo title';
        $articleData->seoMetaDescription = 'Demonstrative seo description';
        $articleData->seoH1 = 'Demonstrative seo H1';
        $articleData->createdAt = new DateTime('2000-01-01T01:01:01');

        $article = $this->articleFactory->create($articleData);

        $this->em->persist($article);
        $this->em->flush();

        $articleId = $article->getId();

        $this->em->clear();

        /** @var \App\Model\Article\Article $refreshedArticle */
        $refreshedArticle = $this->em->getRepository(Article::class)->find($articleId);

        $this->assertSame('Demonstrative name', $refreshedArticle->getName());
        $this->assertSame('topMenu', $refreshedArticle->getPlacement());
        $this->assertSame('Demonstrative seo title', $refreshedArticle->getSeoTitle());
        $this->assertSame('Demonstrative seo description', $refreshedArticle->getSeoMetaDescription());
        $this->assertSame('Demonstrative seo H1', $refreshedArticle->getSeoH1());
        $this->assertEquals(new DateTime('2000-01-01T01:01:01'), $refreshedArticle->getCreatedAt());
    }
}
