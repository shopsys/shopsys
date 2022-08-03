<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

/**
 * @phpstan-import-type CollectedDataArray from \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchRequestCollection
 */
class ElasticsearchCollector extends DataCollector
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchRequestCollection
     */
    protected $elasticsearchRequestCollection;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchRequestCollection $elasticsearchRequestCollection
     */
    public function __construct(ElasticsearchRequestCollection $elasticsearchRequestCollection)
    {
        $this->elasticsearchRequestCollection = $elasticsearchRequestCollection;
    }

    /**
     * @inheritdoc
     */
    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data = [
            'requests' => $this->elasticsearchRequestCollection->getCollectedData(),
            'requestsCount' => $this->elasticsearchRequestCollection->getCollectedDataCount(),
            'totalRequestsTime' => $this->elasticsearchRequestCollection->getTotalTime() * 1000,
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'shopsys.elasticsearch_collector';
    }

    /**
     * @return array{
     *     requests: CollectedDataArray[],
     *     requestsCount: int,
     *     totalRequestsTime: float|int
     * }
     */
    public function getData(): array
    {
        return $this->data;
    }
}
