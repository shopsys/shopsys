<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;

class ProductOrderingModeProvider
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    public function getOrderingModeFromArgument(Argument $argument): string
    {
        $orderingMode = $this->getDefaultOrderingMode($argument);

        if ($argument->offsetExists('orderingMode') && $argument->offsetGet('orderingMode') !== null) {
            $orderingMode = $argument->offsetGet('orderingMode');
        }

        return $orderingMode;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    public function getDefaultOrderingMode(Argument $argument): string
    {
        if (isset($argument['searchInput']['search'])) {
            return $this->getDefaultOrderingModeForSearch();
        }

        return $this->getDefaultOrderingModeForListing();
    }

    /**
     * @return string
     */
    public function getDefaultOrderingModeForListing(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }

    /**
     * @return string
     */
    public function getDefaultOrderingModeForSearch(): string
    {
        return ProductListOrderingConfig::ORDER_BY_RELEVANCE;
    }
}
