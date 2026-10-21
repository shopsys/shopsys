<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Elasticsearch;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;

class ElasticsearchSearchBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly MultipleSearchRepository $multipleSearchRepository,
    ) {
    }

    /**
     * @param array<int, array{index: string, body: array<string, mixed>}> $searchQueries
     */
    public function loadByQueries(array $searchQueries): Promise
    {
        return $this->promiseAdapter->all($this->multipleSearchRepository->searchIndexedByKey($searchQueries));
    }
}
