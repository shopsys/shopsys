<?php

declare(strict_types=1);

namespace App\Form\Constraints;

use App\Model\Product\Product;
use Symfony\Component\Validator\Constraint;

class UniqueProductCatnum extends Constraint
{
    public string $message = 'Produkt s tímto katalogovým číslem již existuje';

    public ?Product $product = null;
}
