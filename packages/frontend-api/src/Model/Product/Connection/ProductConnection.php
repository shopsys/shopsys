<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection;

use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;

class ProductConnection extends Connection
{
    /**
     * @var callable
     */
    protected $productFilterOptionsClosure;

    /**
     * @param \Overblog\GraphQLBundle\Relay\Connection\EdgeInterface[] $edges
     * @param \Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface|null $pageInfo
     * @param int $totalCount
     * @param callable $productFilterOptionsClosure
     */
    public function __construct(
        array $edges,
        ?PageInfoInterface $pageInfo,
        int $totalCount,
        callable $productFilterOptionsClosure,
    ) {
        parent::__construct($edges, $pageInfo);

        $this->totalCount = $totalCount;
        $this->productFilterOptionsClosure = $productFilterOptionsClosure;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function getProductFilterOptions(): ProductFilterOptions
    {
        return ($this->productFilterOptionsClosure)();
    }
}
