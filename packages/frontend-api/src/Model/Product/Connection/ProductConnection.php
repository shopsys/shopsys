<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection;

use Closure;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;

class ProductConnection extends Connection
{
    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param \Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface|null $pageInfo
     * @param \Closure $productFilterOptionsClosure
     * @param string|null $orderingMode
     * @param null $totalCount
     * @param string $defaultOrderingMode
     */
    public function __construct(
        array $edges,
        ?PageInfoInterface $pageInfo,
        protected readonly Closure $productFilterOptionsClosure,
        protected readonly ?string $orderingMode = null,
        protected $totalCount = null,
        protected readonly string $defaultOrderingMode = ProductListOrderingConfig::ORDER_BY_PRIORITY,
    ) {
        parent::__construct($edges, $pageInfo);
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function getProductFilterOptions(): ProductFilterOptions
    {
        return ($this->productFilterOptionsClosure)();
    }

    /**
     * @return string|null
     */
    public function getOrderingMode(): ?string
    {
        return $this->orderingMode;
    }

    /**
     * @return string
     */
    public function getDefaultOrderingMode(): string
    {
        return $this->defaultOrderingMode;
    }
}
