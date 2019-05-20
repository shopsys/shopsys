<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ElasticsearchCollector extends DataCollector
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchDebugStack
     */
    protected $elasticsearchDebugStack;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Debug\ElasticsearchDebugStack $elasticSearchDebugStack
     */
    public function __construct(ElasticsearchDebugStack $elasticSearchDebugStack)
    {
        $this->elasticsearchDebugStack = $elasticSearchDebugStack;
    }

    /**
     * @inheritdoc
     */
    public function collect(Request $request, Response $response, Exception $exception = null): void
    {
        $totalRequestsTime = 0;
        $collectedData = $this->elasticsearchDebugStack->getCollectedData();
        foreach ($collectedData as $requestData) {
            $totalRequestsTime += $requestData['duration'];
        }

        $this->data = [
            'requests' => $collectedData,
            'requestsCount' => count($collectedData),
            'totalRequestsTime' => $totalRequestsTime,
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
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
