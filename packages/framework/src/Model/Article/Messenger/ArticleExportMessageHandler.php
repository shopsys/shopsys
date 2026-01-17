<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Messenger;

use Exception;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleIndex;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ArticleExportMessageHandler
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexRegistry $indexRegistry,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
    ) {
    }

    public function __invoke(ArticleExportMessage $articleExportMessage): void
    {
        try {
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ArticleIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ArticleIndex::getName(), $articleExportMessage->domainId),
                [$articleExportMessage->articleId],
            );

            $this->logger->info('Article successfully exported to Elasticsearch', [
                'articleId' => $articleExportMessage->articleId,
            ]);
        } catch (Exception $exception) {
            $this->logger->error('Exporting article to Elasticsearch failed', [
                'articleId' => $articleExportMessage->articleId,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
