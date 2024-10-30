<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Validator\Constraint;

class UniqueProductCatnum extends Constraint
{
    public string $message = 'Product with entered catalog number already exists';

    public ?Product $product = null;
}
