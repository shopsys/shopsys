<?php

declare(strict_types=1);

namespace App\Form\Constraints;

use App\Model\Product\Product;
use Symfony\Component\Validator\Constraint;

class UniqueProductCatnum extends Constraint
{
    public string $message = 'Product with entered catalog number already exists';

    public ?Product $product = null;
}
