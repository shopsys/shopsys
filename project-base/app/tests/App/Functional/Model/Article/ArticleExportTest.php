<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Article;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleExportRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

final class ArticleExportTest extends TransactionFunctionalTestCase
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
    private ContentDocumentFactory $contentDocumentFactory;

    /**
     * @inject
     */
    private ArticleExportRepository $articleExportRepository;

    public function testExportedArticleHasPlainTextAndStorefrontContent(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $articleData = $this->articleDataFactory->create(Domain::FIRST_DOMAIN_ID);
        $articleData->name = 'Exported article';
        $articleData->placement = Article::PLACEMENT_FOOTER_1;
        $articleData->content = $this->contentDocumentFactory->createFromArray([
            'blocks' => [
                ['type' => 'text', 'data' => ['html' => '<p>Findable <strong>word</strong></p>']],
                ['type' => 'products', 'data' => ['productIds' => [$product->getId()]]],
            ],
        ]);
        $article = $this->articleFacade->create($articleData);

        $extractedArticle = $this->articleExportRepository->extractArticle($article);

        $this->assertSame('Findable word', $extractedArticle['text']);
        $this->assertSame(['text', 'products'], array_column($extractedArticle['content']['blocks'], 'type'));
        $this->assertSame([$product->getCatnum()], $extractedArticle['content']['blocks'][1]['data']['catnums']);
    }
}
