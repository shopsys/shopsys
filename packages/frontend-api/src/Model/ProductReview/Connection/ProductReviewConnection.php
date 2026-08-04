<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview\Connection;

use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;

class ProductReviewConnection extends Connection
{
    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param \Closure(): array{average_rating: float|null, total_count: int, rating_counts: array<int, array{rating: int, count: int}>} $summaryClosure
     */
    public function __construct(
        array $edges,
        ?PageInfoInterface $pageInfo,
        protected readonly Closure $summaryClosure,
        protected readonly string $orderingMode,
        int|Promise|null $totalCount = null,
    ) {
        parent::__construct($edges, $pageInfo);

        $this->totalCount = $totalCount;
    }

    /**
     * @return array{average_rating: float|null, total_count: int, rating_counts: array<int, array{rating: int, count: int}>}
     */
    public function getSummary(): array
    {
        return ($this->summaryClosure)();
    }

    public function getOrderingMode(): string
    {
        return $this->orderingMode;
    }
}
