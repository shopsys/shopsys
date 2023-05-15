<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexAlreadyExistsException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchNoAliasException;
use Symfony\Component\Console\Output\OutputInterface;

class IndexFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly IndexRepository $indexRepository,
        protected readonly ProgressBarFactory $progressBarFactory,
        protected readonly SqlLoggerFacade $sqlLoggerFacade,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function create(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $alias = $indexDefinition->getIndexAlias();

        try {
            if (!$this->isIndexUpToDate($indexDefinition)) {
                throw new ElasticsearchIndexException(sprintf(
                    'There is an index for alias "%s" already. You have to migrate it first due to different definition.',
                    $alias,
                ));
            }
        } catch (ElasticsearchNoAliasException $exception) {
            $output->writeln(sprintf('Alias "%s" does not exist', $alias));
        }

        $this->createIndexWhenNeeded($indexDefinition, $output);
        $this->createAlias($indexDefinition, $output);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function delete(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Deleting index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $this->indexRepository->deleteIndexByIndexDefinition($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function export(AbstractIndex $index, IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Exporting data of "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $this->createIndexWhenNoAliasFound($indexDefinition, $output);

        if (!$this->isIndexUpToDate($indexDefinition)) {
            $this->migrate($indexDefinition, $output);
        }

        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $domainId = $indexDefinition->getDomainId();
        $progressBar = $this->progressBarFactory->create(
            $output,
            $index->getTotalCount($indexDefinition->getDomainId()),
        );

        $exportedIds = [];
        $lastProcessedId = 0;

        do {
            // detach objects from manager to prevent memory leaks
            $this->entityManager->clear();
            $currentBatchData = $index->getExportDataForBatch(
                $domainId,
                $lastProcessedId,
                $index->getExportBatchSize(),
            );
            $currentBatchSize = count($currentBatchData);

            if ($currentBatchSize === 0) {
                break;
            }

            $this->indexRepository->bulkUpdate($indexDefinition->getIndexAlias(), $currentBatchData);
            $progressBar->advance($currentBatchSize);

            $exportedIds = array_merge($exportedIds, array_keys($currentBatchData));
            $lastProcessedId = array_key_last($currentBatchData);
        } while ($currentBatchSize >= $index->getExportBatchSize());

        $this->indexRepository->deleteNotPresent($indexDefinition, $exportedIds);

        $progressBar->finish();
        $output->writeln('');

        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function exportChanged(AbstractIndex $index, IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        if (!$index instanceof IndexSupportChangesOnlyInterface) {
            $output->writeln(
                sprintf(
                    'Index "%s" does not support export of only changed rows. Skipping.',
                    $indexDefinition->getIndexName(),
                ),
            );

            return;
        }

        try {
            $this->resolveExistingIndexName($indexDefinition);
        } catch (ElasticsearchNoAliasException $exception) {
            throw new ElasticsearchIndexException(sprintf(
                'Can\'t found any index with alias "%s". You have to migrate elasticsearch structure first.',
                $indexDefinition->getIndexAlias(),
            ));
        }

        $output->writeln(sprintf(
            'Exporting changed data of "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $progressBar = $this->progressBarFactory->create(
            $output,
            $index->getChangedCount($indexDefinition->getDomainId()),
        );

        $lastProcessedId = 0;

        while (true) {
            $changedIdsBatch = $index->getChangedIdsForBatch(
                $indexDefinition->getDomainId(),
                $lastProcessedId,
                $index->getExportBatchSize(),
            );

            if ($changedIdsBatch === []) {
                break;
            }

            $this->exportIds($index, $indexDefinition, $changedIdsBatch);

            $progressBar->advance(count($changedIdsBatch));
            $lastProcessedId = end($changedIdsBatch);
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function migrate(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $indexName = $indexDefinition->getIndexName();
        $domainId = $indexDefinition->getDomainId();

        try {
            $existingIndexName = $this->resolveExistingIndexName($indexDefinition);
        } catch (ElasticsearchNoAliasException $exception) {
            $output->writeln(sprintf('No index for alias "%s" was not found on domain "%s"', $indexName, $domainId));
            $this->create($indexDefinition, $output);

            return;
        }

        if ($existingIndexName === $indexDefinition->getVersionedIndexName()) {
            $output->writeln(sprintf('Index "%s" on domain "%s" is up to date', $indexName, $domainId));

            return;
        }

        $output->writeln(sprintf('Migrating index "%s" on domain "%s"', $indexName, $domainId));

        $this->createIndexWhenNeeded($indexDefinition, $output);
        $this->indexRepository->reindex($existingIndexName, $indexDefinition->getVersionedIndexName());
        $this->createAlias($indexDefinition, $output);
        $this->indexRepository->deleteIndex($existingIndexName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param int[] $restrictToIds
     */
    public function exportIds(AbstractIndex $index, IndexDefinition $indexDefinition, array $restrictToIds): void
    {
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $indexAlias = $indexDefinition->getIndexAlias();
        $domainId = $indexDefinition->getDomainId();

        $chunkedIdsToExport = array_chunk($restrictToIds, $index->getExportBatchSize());

        foreach ($chunkedIdsToExport as $idsToExport) {
            // detach objects from manager to prevent memory leaks
            $this->entityManager->clear();
            $currentBatchData = $index->getExportDataForIds($domainId, $idsToExport);

            if (count($currentBatchData) > 0) {
                $this->indexRepository->bulkUpdate($indexAlias, $currentBatchData);
            }

            $idsToDelete = array_values(array_diff($idsToExport, array_keys($currentBatchData)));

            if (count($idsToDelete) > 0) {
                $this->indexRepository->deleteIds($indexAlias, $idsToDelete);
            }
        }

        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createIndexWhenNoAliasFound(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        try {
            $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());
        } catch (ElasticsearchNoAliasException $exception) {
            $output->writeln(sprintf(
                'Index "%s" does not exist on domain "%s"',
                $indexDefinition->getIndexName(),
                $indexDefinition->getDomainId(),
            ));
            $this->create($indexDefinition, $output);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createIndexWhenNeeded(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        try {
            $this->indexRepository->createIndex($indexDefinition);
        } catch (ElasticsearchIndexAlreadyExistsException $exception) {
            $output->writeln(sprintf(
                'Index "%s" was not created on domain "%s" because it already exists',
                $indexDefinition->getIndexName(),
                $indexDefinition->getDomainId(),
            ));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @return string
     */
    protected function resolveExistingIndexName(IndexDefinition $indexDefinition): string
    {
        return $this->indexRepository->findCurrentIndexNameForAlias(
            $indexDefinition->getIndexAlias(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function createAlias(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating alias for index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $this->indexRepository->createAlias($indexDefinition);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @return bool
     */
    protected function isIndexUpToDate(IndexDefinition $indexDefinition): bool
    {
        $existingIndexName = $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());

        return $existingIndexName === $indexDefinition->getVersionedIndexName();
    }
}
