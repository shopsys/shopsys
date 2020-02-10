<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository;
use Symfony\Component\Console\Output\NullOutput;
use Tests\FrameworkBundle\Unit\Component\Elasticsearch\__fixtures\CategoryIndex;

class IndexFacadeTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    private function getIndexDefinitionMock(): IndexDefinition
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->createMock(IndexDefinition::class);
        $indexDefinitionMock->method('getDomainId')->willReturn(1);
        return $indexDefinitionMock;
    }

    public function testCreateByIndexDefinitionCreatesIndexAndAlias(): void
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->expects($this->once())->method('createIndex');
        $indexRepositoryMock->expects($this->once())->method('createAlias');

        $indexFacade = new IndexFacade($indexRepositoryMock);
        $indexFacade->createByIndexDefinition($this->getIndexDefinitionMock(), new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $indexRepositoryMock->expects($this->once())->method('createIndex');
        $indexRepositoryMock->expects($this->once())->method('reindex')->with($oldIndexName, $newIndexName);
        $indexRepositoryMock->expects($this->once())->method('createAlias');
        $indexRepositoryMock->expects($this->once())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMock();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($newIndexName);

        $indexFacade = new IndexFacade($indexRepositoryMock);
        $indexFacade->migrateByIndexDefinition($indexDefinitionMock, new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNotNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $indexRepositoryMock->expects($this->never())->method('createIndex');
        $indexRepositoryMock->expects($this->never())->method('reindex')->with($oldIndexName, $newIndexName);
        $indexRepositoryMock->expects($this->never())->method('createAlias');
        $indexRepositoryMock->expects($this->never())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMock();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($oldIndexName);

        $indexFacade = new IndexFacade($indexRepositoryMock);
        $indexFacade->migrateByIndexDefinition($indexDefinitionMock, new NullOutput());
    }
}
