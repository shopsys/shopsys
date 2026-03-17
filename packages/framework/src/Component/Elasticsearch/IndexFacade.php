<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchAliasNotFoundException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexAliasAlreadyExistsException;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexAliasNotFoundException;
use Symfony\Component\Console\Output\OutputInterface;

class IndexFacade
{
    public function __construct(
        protected readonly IndexRepository $indexRepository,
        protected readonly ProgressBarFactory $progressBarFactory,
        protected readonly SqlLoggerFacade $sqlLoggerFacade,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

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
                throw new ElasticsearchIndexAliasAlreadyExistsException(sprintf(
                    'There is an index for alias "%s" already. You have to migrate it first due to different definition.',
                    $alias,
                ));
            }
        } catch (ElasticsearchAliasNotFoundException $exception) {
            $output->writeln(sprintf('Alias "%s" does not exist', $alias));
        }

        $this->createIndexWhenNeeded($indexDefinition, $output);
        $this->createAlias($indexDefinition, $output);
    }

    public function delete(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Deleting index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $this->indexRepository->deleteIndexByIndexDefinition($indexDefinition);
    }

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

        try {
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
        } finally {
            $this->sqlLoggerFacade->reenableLogging();
        }
    }

    public function exportChanged(
        AbstractIndex $index,
        IndexDefinition $indexDefinition,
        OutputInterface $output,
    ): void {
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
        } catch (ElasticsearchAliasNotFoundException $exception) {
            throw new ElasticsearchIndexAliasNotFoundException(sprintf(
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
            $lastProcessedId = array_last($changedIdsBatch);
        }

        $progressBar->finish();
        $output->writeln('');
    }

    public function migrate(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $indexName = $indexDefinition->getIndexName();
        $domainId = $indexDefinition->getDomainId();

        try {
            $existingIndexName = $this->resolveExistingIndexName($indexDefinition);
        } catch (ElasticsearchAliasNotFoundException $exception) {
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
     * @param int[] $restrictToIds
     * @param string[] $fields
     */
    public function exportIds(
        AbstractIndex $index,
        IndexDefinition $indexDefinition,
        array $restrictToIds,
        array $fields = AbstractIndex::ALL_FIELDS,
    ): void {
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        try {
            $indexAlias = $indexDefinition->getIndexAlias();
            $domainId = $indexDefinition->getDomainId();

            $chunkedIdsToExport = array_chunk($restrictToIds, $index->getExportBatchSize());

            foreach ($chunkedIdsToExport as $idsToExport) {
                // detach objects from manager to prevent memory leaks
                $this->entityManager->clear();
                $currentBatchData = $index->getExportDataForIds($domainId, $idsToExport, $fields);

                if (count($currentBatchData) > 0) {
                    $this->indexRepository->bulkUpdate($indexAlias, $currentBatchData);
                }

                $idsToDelete = $currentBatchData
                    |> array_keys(...)
                    |> (fn ($v) => array_diff($idsToExport, $v))
                    |> array_values(...);

                if (count($idsToDelete) > 0) {
                    $this->indexRepository->deleteIds($indexAlias, $idsToDelete);
                }
            }
        } finally {
            $this->sqlLoggerFacade->reenableLogging();
        }
    }

    protected function createIndexWhenNoAliasFound(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        try {
            $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());
        } catch (ElasticsearchAliasNotFoundException $exception) {
            $output->writeln(sprintf(
                'Index "%s" does not exist on domain "%s"',
                $indexDefinition->getIndexName(),
                $indexDefinition->getDomainId(),
            ));
            $this->create($indexDefinition, $output);
        }
    }

    protected function createIndexWhenNeeded(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        try {
            $this->indexRepository->createIndex($indexDefinition);
        } catch (ElasticsearchIndexAliasAlreadyExistsException $exception) {
            $output->writeln(sprintf(
                'Index "%s" was not created on domain "%s" because it already exists',
                $indexDefinition->getIndexName(),
                $indexDefinition->getDomainId(),
            ));
        }
    }

    protected function resolveExistingIndexName(IndexDefinition $indexDefinition): string
    {
        return $this->indexRepository->findCurrentIndexNameForAlias(
            $indexDefinition->getIndexAlias(),
        );
    }

    protected function createAlias(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating alias for index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId(),
        ));

        $this->indexRepository->createAlias($indexDefinition);
    }

    protected function isIndexUpToDate(IndexDefinition $indexDefinition): bool
    {
        $existingIndexName = $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());

        return $existingIndexName === $indexDefinition->getVersionedIndexName();
    }
}
