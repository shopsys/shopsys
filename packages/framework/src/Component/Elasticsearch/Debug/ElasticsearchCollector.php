<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Debug;

use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class ElasticsearchCollector extends DataCollector
{
    public function __construct(protected readonly ElasticsearchRequestCollection $elasticsearchRequestCollection)
    {
    }

    #[Override]
    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data = [
            'requests' => $this->elasticsearchRequestCollection->getCollectedData(),
            'requestsCount' => $this->elasticsearchRequestCollection->getCollectedDataCount(),
            'totalRequestsTime' => $this->elasticsearchRequestCollection->getTotalTime() * 1000,
        ];
    }

    #[Override]
    public function reset(): void
    {
        $this->data = [];
    }

    #[Override]
    public function getName(): string
    {
        return 'shopsys.elasticsearch_collector';
    }

    public function getData(): array
    {
        return $this->data;
    }
}
