<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

class DispatchAllProductsMessage
{
    /**
     * @param string[] $exportScopes
     */
    public function __construct(public readonly array $exportScopes = [])
    {
    }
}
