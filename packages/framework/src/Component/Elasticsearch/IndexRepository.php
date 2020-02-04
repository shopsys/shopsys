<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Exception\ElasticsearchIndexException;
use Symfony\Component\Console\Output\OutputInterface;

class IndexRepository
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $elasticsearchClient;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    protected $progressBarFactory;

    /**
     * @param \Elasticsearch\Client $elasticsearchClient
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     */
    public function __construct(Client $elasticsearchClient, ProgressBarFactory $progressBarFactory)
    {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function createIndex(IndexDefinition $indexDefinition): void
    {
        $indexName = $indexDefinition->getVersionedIndexName();
        $indexes = $this->elasticsearchClient->indices();

        if ($indexes->exists(['index' => $indexName])) {
            throw ElasticsearchIndexException::indexAlreadyExists($indexName);
        }

        $result = $indexes->create([
            'index' => $indexName,
            'body' => $indexDefinition->getDefinition(),
        ]);

        if (isset($result['error'])) {
            throw ElasticsearchIndexException::createIndexError($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function createAlias(IndexDefinition $indexDefinition): void
    {
        $indexName = $indexDefinition->getVersionedIndexName();
        $indexes = $this->elasticsearchClient->indices();

        $result = $indexes->putAlias([
            'index' => $indexName,
            'name' => $indexDefinition->getIndexAlias(),
        ]);

        if (isset($result['error'])) {
            throw ElasticsearchIndexException::createAliasError($indexName, $result['error']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     */
    public function deleteIndexByIndexDefinition(IndexDefinition $indexDefinition): void
    {
        $indexes = $this->elasticsearchClient->indices();
        foreach ($indexes->getAliases() as $indexName => list('aliases' => $aliases)) {
            if (array_key_exists($indexDefinition->getIndexAlias(), $aliases)) {
                $this->deleteIndex($indexName);
            }
        }
    }

    /**
     * @param string $indexName
     */
    public function deleteIndex(string $indexName): void
    {
        $indexes = $this->elasticsearchClient->indices();

        $result = $indexes->delete([
            'index' => $indexName,
        ]);

        if (isset($result['error'])) {
            throw ElasticsearchIndexException::deleteIndexError($indexName, $result['error']);
        }
    }

    /**
     * @param string $indexAlias
     * @param int $domainId
     * @param array $ids
     */
    public function deleteIds(string $indexAlias, int $domainId, array $ids): void
    {
        $this->elasticsearchClient->deleteByQuery([
            'index' => $indexAlias,
            'type' => '_doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'ids' => [
                                'values' => array_values($ids),
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param string $indexAlias
     * @param array $data
     * @param bool $createIfNotExists
     */
    public function bulkUpdate(string $indexAlias, array $data, bool $createIfNotExists = true): void
    {
        $params = ['body' => []];

        foreach ($data as $id => $row) {
            $params['body'][] = [
                'update' => [
                    '_index' => $indexAlias,
                    '_type' => '_doc',
                    '_id' => (string)$id,
                ],
            ];

            $params['body'][] = [
                'doc' => $row,
                'doc_as_upsert' => $createIfNotExists,
            ];
        }

        $result = $this->elasticsearchClient->bulk($params);

        if (isset($result['errors']) && $result['errors'] === true) {
            throw ElasticsearchIndexException::bulkUpdateError($indexAlias, $result['items']);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition $indexDefinition
     * @param array $restrictToIds
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function export(IndexDefinition $indexDefinition, array $restrictToIds, OutputInterface $output): void
    {
        $indexAlias = $indexDefinition->getIndexAlias();
        $dataProvider = $indexDefinition->getIndex()->getDataProvider();
        $domainId = $indexDefinition->getDomainId();

        if ($restrictToIds !== []) {
            $totalCount = count($restrictToIds);
        } else {
            $totalCount = $dataProvider->getTotalCount($indexDefinition->getDomainId());
        }

        $progressBar = $this->progressBarFactory->create($output, $totalCount);

        $lastProcessedId = 0;
        $updatedIds = [];
        do {
            $currentBatchData = $dataProvider->getDataForBatch($domainId, $lastProcessedId, $restrictToIds);
            $currentBatchSize = count($currentBatchData);
            if (empty($currentBatchData)) {
                break;
            }

            $this->bulkUpdate($indexAlias, $currentBatchData);
            $progressBar->advance($currentBatchSize);

            $updatedIds = array_merge($updatedIds, array_keys($currentBatchData));
            $lastProcessedId = array_key_last($currentBatchData);
        } while ($currentBatchSize >= DataProviderInterface::BATCH_SIZE);

        $idsToDelete = array_diff($restrictToIds, $updatedIds);
        $this->deleteIds($indexAlias, $domainId, $idsToDelete);

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param string $aliasName
     * @return array
     */
    protected function findIndexNamesForAlias(string $aliasName): array
    {
        if (!$this->isAliasCreated($aliasName)) {
            throw ElasticsearchIndexException::aliasDoesntExists($aliasName);
        }

        $indexes = $this->elasticsearchClient->indices();

        $indexesWithAlias = array_keys($indexes->getAlias(['name' => $aliasName]));
        if (empty($indexesWithAlias)) {
            throw ElasticsearchIndexException::noIndexFoundForAlias($aliasName);
        }

        return $indexesWithAlias;
    }

    /**
     * @param string $aliasName
     * @return bool
     */
    protected function isAliasCreated(string $aliasName): bool
    {
        $indexes = $this->elasticsearchClient->indices();
        return $indexes->existsAlias(['name' => $aliasName]);
    }

    /**
     * @param string $aliasName
     * @return string
     */
    public function findCurrentIndexNameForAlias(string $aliasName): string
    {
        $indexesWithAlias = $this->findIndexNamesForAlias($aliasName);

        if (count($indexesWithAlias) > 1) {
            throw ElasticsearchIndexException::moreThanOneIndexFoundForAlias($aliasName, $indexesWithAlias);
        }

        return $indexesWithAlias[0];
    }

    /**
     * @param string $sourceIndexName
     * @param string $destinationIndexName
     */
    public function reindex(string $sourceIndexName, string $destinationIndexName): void
    {
        $this->elasticsearchClient->reindex([
            'body' => [
                'source' => [
                    'index' => $sourceIndexName,
                ],
                'dest' => [
                    'index' => $destinationIndexName,
                ],
            ],
        ]);
    }
}
