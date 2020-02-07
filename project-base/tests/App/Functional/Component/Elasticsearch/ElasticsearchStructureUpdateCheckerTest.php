<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Tests\App\Test\FunctionalTestCase;

final class ElasticsearchStructureUpdateCheckerTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureUpdateChecker
     * @inject
     */
    private $elasticsearchStructureUpdateChecker;

    /**
     * @var \Elasticsearch\Client
     * @inject
     */
    private $elasticsearchClient;

    /**
     * @var \Elasticsearch\Namespaces\IndicesNamespace
     */
    private $elasticsearchIndexes;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager
     * @inject
     */
    private $elasticsearchStructureManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->elasticsearchIndexes = $this->elasticsearchClient->indices();
    }

    /**
     * @return iterable
     */
    public function elasticseachIndexesParametersProvider(): iterable
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            yield [$domainId, ProductElasticsearchRepository::ELASTICSEARCH_INDEX];
        }
    }

    public function testUpdateIsNotNecessaryWhenNothingIsChanged(): void
    {
        foreach ($this->elasticseachIndexesParametersProvider() as $dataProvider) {
            $domainId = $dataProvider[0];
            $index = $dataProvider[1];

            $definition = $this->elasticsearchStructureManager->getStructureDefinition($domainId, $index);
            $aliasName = $this->elasticsearchStructureManager->getAliasName($domainId, $index);
            $indexName = $this->elasticsearchStructureManager->getCurrentIndexName($domainId, $index);

            $this->createNewStructureAndMakeBackup($definition, $definition, $indexName, $aliasName);

            try {
                $this->assertFalse(
                    $this->elasticsearchStructureUpdateChecker->isNecessaryToUpdateStructure($domainId, $index)
                );
            } finally {
                $this->revertStructureFromBackup($definition, $indexName, $aliasName);
            }
        }
    }

    public function testUpdateIsNecessaryWhenStructureHasAdditionalProperty(): void
    {
        foreach ($this->elasticseachIndexesParametersProvider() as $dataProvider) {
            $domainId = $dataProvider[0];
            $index = $dataProvider[1];

            $oldDefinition = $this->elasticsearchStructureManager->getStructureDefinition($domainId, $index);
            $aliasName = $this->elasticsearchStructureManager->getAliasName($domainId, $index);
            $indexName = $this->elasticsearchStructureManager->getCurrentIndexName($domainId, $index);

            $newDefinition = $oldDefinition;
            $newDefinition['mappings']['_doc']['properties']['new_property'] = ['type' => 'text'];

            $this->createNewStructureAndMakeBackup($oldDefinition, $newDefinition, $indexName, $aliasName);

            try {
                $this->assertTrue(
                    $this->elasticsearchStructureUpdateChecker->isNecessaryToUpdateStructure($domainId, $index)
                );
            } finally {
                $this->revertStructureFromBackup($oldDefinition, $indexName, $aliasName);
            }
        }
    }

    /**
     * @param array $oldDefinition
     * @param array $newDefinition
     * @param string $indexName
     * @param string $aliasName
     */
    private function createNewStructureAndMakeBackup(array $oldDefinition, array $newDefinition, string $indexName, string $aliasName): void
    {
        $backupIndexName = $indexName . '_backup';
        $this->moveStructureByReindexing($indexName, $backupIndexName, $oldDefinition);
        $this->elasticsearchIndexes->create(['index' => $indexName, 'body' => $newDefinition]);
        $this->elasticsearchIndexes->putAlias(['index' => $indexName, 'name' => $aliasName]);
    }

    /**
     * @param array $oldDefinition
     * @param string $indexName
     * @param string $aliasName
     */
    private function revertStructureFromBackup(array $oldDefinition, string $indexName, string $aliasName): void
    {
        $backupIndexName = $indexName . '_backup';
        $this->moveStructureByReindexing($backupIndexName, $indexName, $oldDefinition);
        $this->elasticsearchIndexes->putAlias(['index' => $indexName, 'name' => $aliasName]);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @param array $definition
     */
    private function moveStructureByReindexing(string $oldName, string $newName, array $definition): void
    {
        if ($this->elasticsearchIndexes->exists(['index' => $newName]) === true) {
            $this->elasticsearchIndexes->delete(['index' => $newName]);
        }
        $this->elasticsearchIndexes->create([
            'index' => $newName,
            'body' => $definition,
        ]);
        $this->elasticsearchClient->reindex([
            'body' => [
                'source' => ['index' => $oldName],
                'dest' => ['index' => $newName],
            ],
            'refresh' => true,
            'wait_for_completion' => true,
        ]);

        $this->elasticsearchIndexes->delete(['index' => $oldName]);
    }
}
