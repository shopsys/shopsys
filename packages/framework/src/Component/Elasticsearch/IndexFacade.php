<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Console\Output\OutputInterface;

class IndexFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository
     */
    protected $indexRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    protected $progressBarFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    protected $sqlLoggerFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository $indexRepository
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        IndexRepository $indexRepository,
        ProgressBarFactory $progressBarFactory,
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $entityManager
    ) {
        $this->indexRepository = $indexRepository;
        $this->progressBarFactory = $progressBarFactory;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function create(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $output->writeln(sprintf(
            'Creating index "%s" on domain "%s"',
            $indexDefinition->getIndexName(),
            $indexDefinition->getDomainId()
        ));

        $this->indexRepository->createIndex($indexDefinition);
        $this->indexRepository->createAlias($indexDefinition);
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
            $indexDefinition->getDomainId()
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
            $indexDefinition->getDomainId()
        ));

        $this->sqlLoggerFacade->temporarilyDisableLogging();

        $indexAlias = $indexDefinition->getIndexAlias();
        $domainId = $indexDefinition->getDomainId();
        $progressBar = $this->progressBarFactory->create($output, $index->getTotalCount($indexDefinition->getDomainId()));

        $exportedIds = [];
        $lastProcessedId = 0;
        do {
            // detach objects from manager to prevent memory leaks
            $this->entityManager->clear();
            $currentBatchData = $index->getExportDataForBatch($domainId, $lastProcessedId, $index->getExportBatchSize());
            $currentBatchSize = count($currentBatchData);

            if ($currentBatchSize === 0) {
                break;
            }

            $this->indexRepository->bulkUpdate($indexAlias, $currentBatchData);
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
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function migrate(IndexDefinition $indexDefinition, OutputInterface $output): void
    {
        $indexName = $indexDefinition->getIndexName();
        $domainId = $indexDefinition->getDomainId();
        $existingIndexName = $this->indexRepository->findCurrentIndexNameForAlias($indexDefinition->getIndexAlias());

        if ($existingIndexName === $indexDefinition->getVersionedIndexName()) {
            $output->writeln(sprintf('Index "%s" on domain "%s" is up to date', $indexName, $domainId));
            return;
        }

        $output->writeln(sprintf('Migrating index "%s" on domain "%s"', $indexName, $domainId));
        $this->indexRepository->createIndex($indexDefinition);
        $this->indexRepository->reindex($existingIndexName, $indexDefinition->getVersionedIndexName());
        $this->indexRepository->createAlias($indexDefinition);
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

        // detach objects from manager to prevent memory leaks
        $this->entityManager->clear();
        $currentBatchData = $index->getExportDataForIds($domainId, $restrictToIds);

        if (!empty($currentBatchData)) {
            $this->indexRepository->bulkUpdate($indexAlias, $currentBatchData);
        }

        $idsToDelete = array_values(array_diff($restrictToIds, array_keys($currentBatchData)));
        if (!empty($idsToDelete)) {
            $this->indexRepository->deleteIds($indexAlias, $idsToDelete);
        }

        $this->sqlLoggerFacade->reenableLogging();
    }
}
