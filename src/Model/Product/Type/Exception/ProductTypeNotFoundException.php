<?php

declare(strict_types=1);

namespace App\Model\Product\Type\Exception;

use Exception;

class ProductTypeNotFoundException extends Exception implements ProductTypeException
{
}
