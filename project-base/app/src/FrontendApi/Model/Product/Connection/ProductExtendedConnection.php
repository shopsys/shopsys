<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection as BaseProductConnection;

class ProductExtendedConnection extends BaseProductConnection
{
    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param \Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface|null $pageInfo
     * @param int $totalCount
     * @param callable $productFilterOptionsClosure
     * @param string|null $orderingMode
     */
    public function __construct(
        array $edges,
        ?PageInfoInterface $pageInfo,
        int $totalCount,
        callable $productFilterOptionsClosure,
        private ?string $orderingMode = null,
    ) {
        parent::__construct($edges, $pageInfo, $totalCount, $productFilterOptionsClosure);
    }

    /**
     * @return string|null
     */
    public function getOrderingMode(): ?string
    {
        return $this->orderingMode;
    }
}
