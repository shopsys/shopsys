<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

interface ParameterViewFacadeInterface
{
    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView[]
     */
    public function getAllByProductId(int $productId): array;
}
