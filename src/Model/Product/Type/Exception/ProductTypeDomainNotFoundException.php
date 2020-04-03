<?php

declare(strict_types=1);

namespace App\Model\Product\Type\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductTypeDomainNotFoundException extends NotFoundHttpException implements ProductTypeException
{
}
