<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;

class StoreConnection extends Connection
{
    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param array{latitude: string, longitude: string}|null $searchCoordinates
     */
    public function __construct(
        array $edges = [],
        ?PageInfoInterface $pageInfo = null,
        protected readonly ?array $searchCoordinates = null,
        int|Promise|null $totalCount = null,
    ) {
        parent::__construct($edges, $pageInfo);

        $this->totalCount = $totalCount;
    }

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    public function getSearchCoordinates(): ?array
    {
        return $this->searchCoordinates;
    }
}
