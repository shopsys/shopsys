<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Elasticsearch\Exception\ElasticsearchIndexException;

class IndexRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex[]
     */
    protected $registeredIndexes;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex[] $indexes
     */
    public function __construct(array $indexes)
    {
        foreach ($indexes as $index) {
            $this->registerIndex($index);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex $index
     */
    protected function registerIndex(AbstractIndex $index): void
    {
        $this->registeredIndexes[$index->getName()] = $index;
    }

    /**
     * @param string $indexName
     * @return bool
     */
    public function isIndexRegistered(string $indexName): bool
    {
        return array_key_exists($indexName, $this->registeredIndexes);
    }

    /**
     * @param string $indexName
     * @return \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex
     */
    public function getIndexByIndexName(string $indexName)
    {
        if ($this->isIndexRegistered($indexName)) {
            return $this->registeredIndexes[$indexName];
        }

        throw ElasticsearchIndexException::noRegisteredIndexFound($indexName);
    }

    /**
     * @return string[]
     */
    public function getRegisteredIndexNames(): array
    {
        return array_keys($this->registeredIndexes);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex[]
     */
    public function getRegisteredIndexes(): array
    {
        return $this->registeredIndexes;
    }
}
