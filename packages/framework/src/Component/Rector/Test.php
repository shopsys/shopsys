<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;

class Test
{
    /**
     * @param mixed $varNumber
     * @param string|null $varString
     * @param bool $unknown2
     * @param \App\Model\Product\Product $product2
     */
    public function foo(
        int $varNumber,
        string $varString,
        $unknown,
        $unknown2,
        ?Product $product,
        BaseProduct $product2,
        string|int $mixing,
        array $collection
    ): void {

    }
}
