<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

interface ProductListInputValidatorInterface
{
    /**
     * @param array $input
     */
    public function validateInput(array $input): void;
}
