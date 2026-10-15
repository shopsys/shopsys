<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use Doctrine\DBAL\Connection;
use Psr\Log\NullLogger;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\GrapesJsHtmlConverter;
use Shopsys\FrameworkBundle\Component\ContentEditor\Migration\MigrateGrapesJsContentTask;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tests\App\Test\TransactionFunctionalTestCase;

class MigrateGrapesJsContentTaskTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private Connection $connection;

    /**
     * @inject
     */
    private GrapesJsHtmlConverter $grapesJsHtmlConverter;

    /**
     * @inject
     */
    private ContentDocumentNormalizer $contentDocumentNormalizer;

    /**
     * @inject
     */
    private IndexRegistry $indexRegistry;

    /**
     * @inject
     */
    private IndexDefinitionLoader $indexDefinitionLoader;

    public function testLegacyContentIsConvertedAndSecondRunTouchesNothing(): void
    {
        $articleId = $this->findId('articles', 1);
        $blogTranslationId = $this->findId('blog_article_translations', 1);
        $mailTemplateId = $this->findId('mail_templates', 1);
        $contentDirName = $this->getContainer()->getParameter('shopsys.content_dir_name');

        $this->setLegacyContent('articles', 'legacy_grapesjs_text', $articleId, '<div class="gjs-text-ckeditor"><p>Hello</p></div>'
            . '<img class="web-custom-image image-position-left" src="/' . $contentDirName . '/wysiwyg/test-image.jpg" alt="Test">'
            . '<div class="gjs-products" data-products="UNKNOWN-CATNUM"></div>');
        $this->setLegacyContent('blog_article_translations', 'legacy_grapesjs_description', $blogTranslationId, '<div class="gjs-text-ckeditor"><h1>Title</h1></div>');
        $this->setLegacyContent('mail_templates', 'legacy_grapesjs_body', $mailTemplateId, '<div style="box-sizing: border-box;"><div class="gjs-text-ckeditor">Hi {name}</div>'
            . '<a data-gjs-type="button-link" href="{url}">Go</a></div>');

        $domainCount = count($this->domain->getAll());
        $indexFacade = $this->createMock(IndexFacade::class);
        $indexFacade->expects($this->exactly(2 * $domainCount))->method('export');

        $output = new BufferedOutput();
        $this->createTask($indexFacade)->run(new SymfonyStyle(new ArrayInput([]), $output));

        $article = $this->fetchContent('articles', $articleId);
        $this->assertSame('web', $article['profile']);
        $this->assertSame(['text', 'image'], array_column($article['blocks'], 'type'));
        $this->assertSame('<p>Hello</p>', $article['blocks'][0]['data']['html']);
        $this->assertIsInt($article['blocks'][1]['data']['mediaId']);
        $this->assertStringContainsString('1 of 1 catalog numbers of products are unknown and were dropped', $output->fetch());

        $blogTranslation = $this->fetchContent('blog_article_translations', $blogTranslationId);
        $this->assertSame('<h2>Title</h2>', $blogTranslation['blocks'][0]['data']['html']);

        $mailTemplate = $this->fetchContent('mail_templates', $mailTemplateId);
        $this->assertSame('mail', $mailTemplate['profile']);
        $this->assertSame(['mail-text', 'mail-button'], array_column($mailTemplate['blocks'], 'type'));
        $this->assertSame('{url}', $mailTemplate['blocks'][1]['data']['url']);

        $this->createTask($indexFacade)->run(new SymfonyStyle(new ArrayInput([]), $secondOutput = new BufferedOutput()));

        $this->assertStringContainsString('Converted 0 articles, 0 blog article translations and 0 mail templates.', $secondOutput->fetch());
        $this->assertSame($article, $this->fetchContent('articles', $articleId));
    }

    public function testRowThatCannotBeConvertedIsSkippedAndDoesNotStopTheTask(): void
    {
        $blogTranslationId = $this->findId('blog_article_translations', 1);
        $this->setLegacyContent('blog_article_translations', 'legacy_grapesjs_description', $blogTranslationId, '<div class="gjs-text-ckeditor">Text</div>');
        // the locale of the row has no domain, so its conversion fails
        $this->connection->executeStatement("UPDATE blog_article_translations SET locale = 'xx' WHERE id = :id", ['id' => $blogTranslationId]);

        $output = new BufferedOutput();
        $this->createTask($this->createStub(IndexFacade::class))->run(new SymfonyStyle(new ArrayInput([]), $output));

        $this->assertNull($this->connection->fetchOne('SELECT content FROM blog_article_translations WHERE id = :id', ['id' => $blogTranslationId]));
        $this->assertStringContainsString('There is no domain with the locale "xx".', $output->fetch());
    }

    private function createTask(IndexFacade $indexFacade): MigrateGrapesJsContentTask
    {
        return new MigrateGrapesJsContentTask(
            $this->connection,
            $this->em,
            $this->grapesJsHtmlConverter,
            $this->contentDocumentNormalizer,
            $this->domain,
            $this->indexRegistry,
            $this->indexDefinitionLoader,
            $indexFacade,
            new NullLogger(),
        );
    }

    private function findId(string $table, int $offset): int
    {
        return (int)$this->connection->fetchOne(sprintf('SELECT id FROM %s ORDER BY id LIMIT 1 OFFSET %d', $table, $offset - 1));
    }

    private function setLegacyContent(string $table, string $legacyColumn, int $id, string $html): void
    {
        $this->connection->executeStatement(
            sprintf('UPDATE %s SET content = NULL, %s = :html WHERE id = :id', $table, $legacyColumn),
            ['html' => $html, 'id' => $id],
        );
    }

    /**
     * @return array{profile: string, blocks: array<int, array<string, mixed>>}
     */
    private function fetchContent(string $table, int $id): array
    {
        return json_decode((string)$this->connection->fetchOne(sprintf('SELECT content FROM %s WHERE id = :id', $table), ['id' => $id]), true, 512, JSON_THROW_ON_ERROR);
    }
}
