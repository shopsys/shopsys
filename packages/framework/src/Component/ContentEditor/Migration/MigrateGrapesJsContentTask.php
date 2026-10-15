<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleIndex;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Converts the HTML saved by GrapesJS (the legacy_grapesjs_* columns) to content documents.
 * Rows that already have a content are never touched, so the task can be run repeatedly.
 *
 * Next release: drop the legacy columns in a migration that first fails when something was not converted:
 *   SELECT count(*) FROM articles WHERE content IS NULL AND legacy_grapesjs_text IS NOT NULL (the same for the two other tables)
 *   ALTER TABLE articles DROP COLUMN legacy_grapesjs_text;
 *   ALTER TABLE blog_article_translations DROP COLUMN legacy_grapesjs_description;
 *   ALTER TABLE mail_templates DROP COLUMN legacy_grapesjs_body;
 */
class MigrateGrapesJsContentTask implements PostDeployTaskInterface
{
    private const int BATCH_SIZE = 50;

    /**
     * @var string[][]
     */
    private array $noteRows = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
        private readonly GrapesJsHtmlConverter $grapesJsHtmlConverter,
        private readonly ContentDocumentNormalizer $contentDocumentNormalizer,
        private readonly Domain $domain,
        private readonly IndexRegistry $indexRegistry,
        private readonly IndexDefinitionLoader $indexDefinitionLoader,
        private readonly IndexFacade $indexFacade,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Override]
    public function run(SymfonyStyle $style): void
    {
        $this->noteRows = [];

        $style->section('Articles');
        $articleCount = $this->migrateTable($style, 'article', 'articles', 'legacy_grapesjs_text', ContentProfileEnum::WEB);

        $style->section('Blog articles');
        $blogArticleCount = $this->migrateTable($style, 'blog article translation', 'blog_article_translations', 'legacy_grapesjs_description', ContentProfileEnum::WEB);

        $style->section('Mail templates');
        $mailTemplateCount = $this->migrateTable($style, 'mail template', 'mail_templates', 'legacy_grapesjs_body', ContentProfileEnum::MAIL);

        if ($articleCount > 0) {
            $this->exportToElasticsearch($style, ExtendedClassNameResolver::resolve(ArticleIndex::class)::getName());
        }

        if ($blogArticleCount > 0) {
            $this->exportToElasticsearch($style, ExtendedClassNameResolver::resolve(BlogArticleIndex::class)::getName());
        }

        if ($this->noteRows !== []) {
            $style->section('Content that was not converted completely');
            $style->table(['Entity', 'Id', 'Note'], $this->noteRows);
        }

        $style->success(sprintf(
            'Converted %d articles, %d blog article translations and %d mail templates.',
            $articleCount,
            $blogArticleCount,
            $mailTemplateCount,
        ));
    }

    private function migrateTable(
        SymfonyStyle $style,
        string $entityName,
        string $table,
        string $legacyColumn,
        string $profile,
    ): int {
        $lastId = 0;
        $convertedCount = 0;

        do {
            // a row that fails stays without content, so the batches are walked by id instead of repeating the query
            $rows = $this->connection->fetchAllAssociative(
                sprintf(
                    'SELECT id, %s AS html FROM %s WHERE id > :lastId AND content IS NULL AND %s IS NOT NULL ORDER BY id LIMIT :limit',
                    $legacyColumn,
                    $table,
                    $legacyColumn,
                ),
                ['lastId' => $lastId, 'limit' => self::BATCH_SIZE],
                ['lastId' => ParameterType::INTEGER, 'limit' => ParameterType::INTEGER],
            );

            $rowCount = count($rows);

            foreach ($rows as $row) {
                $lastId = $row['id'];

                try {
                    $notes = $this->migrateRow($table, $row, $profile);
                    $convertedCount++;
                } catch (Throwable $exception) {
                    $notes = ['conversion failed and the row was skipped: ' . $exception->getMessage()];
                    $style->warning(sprintf('%s %d: %s', $entityName, $row['id'], $notes[0]));
                }

                if ($notes !== []) {
                    $this->logger->warning('Legacy GrapesJS content was converted with losses.', ['content_editor_migration' => ['entity' => $entityName, 'id' => $row['id'], 'notes' => $notes]]);
                }

                foreach ($notes as $note) {
                    $this->noteRows[] = [$entityName, (string)$row['id'], $note];
                }
            }

            // the media created by the image import would otherwise pile up in the memory
            $this->entityManager->clear();
            $style->writeln(sprintf('Converted %d %ss...', $convertedCount, $entityName));
        } while ($rowCount === self::BATCH_SIZE);

        return $convertedCount;
    }

    /**
     * @param array<string, mixed> $row
     * @return string[] notes about what was lost
     */
    private function migrateRow(string $table, array $row, string $profile): array
    {
        $result = $this->grapesJsHtmlConverter->convert($row['html'], $profile);
        $document = $this->contentDocumentNormalizer->normalize($result->document);
        $report = $this->contentDocumentNormalizer->getLastReport();

        $this->connection->executeStatement(
            sprintf('UPDATE %s SET content = CAST(:content AS jsonb) WHERE id = :id AND content IS NULL', $table),
            ['content' => $document->toJson(), 'id' => $row['id']],
        );

        $notes = $result->notes;

        foreach ($report->getDroppedBlockTypes() as $type) {
            $notes[] = sprintf('block of the type "%s" is not available and was dropped', $type);
        }

        foreach ($report->getFlattenedContainerTypes() as $type) {
            $notes[] = sprintf('nested block "%s" was flattened', $type);
        }

        return $notes;
    }

    // the stored content is already correct, so a failed export only needs a repeated export and must not fail the task
    private function exportToElasticsearch(SymfonyStyle $style, string $indexName): void
    {
        try {
            foreach ($this->domain->getAll() as $domainConfig) {
                $this->indexFacade->export(
                    $this->indexRegistry->getIndexByIndexName($indexName),
                    $this->indexDefinitionLoader->getIndexDefinition($indexName, $domainConfig->getId()),
                    $style,
                );
            }
        } catch (Throwable $exception) {
            $style->warning(sprintf(
                'Export of the index "%s" failed (%s). Run "php bin/console shopsys:elasticsearch:data-export %s".',
                $indexName,
                $exception->getMessage(),
                $indexName,
            ));
        }
    }
}
