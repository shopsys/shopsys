<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class ProductNotFoundException extends GoneHttpException implements ProductException
{
}
