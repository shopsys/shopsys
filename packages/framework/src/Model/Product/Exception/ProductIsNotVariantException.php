<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductIsNotVariantException extends NotFoundHttpException implements ProductException
{
}
