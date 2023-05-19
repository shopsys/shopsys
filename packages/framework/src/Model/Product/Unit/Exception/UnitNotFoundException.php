<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnitNotFoundException extends NotFoundHttpException implements UnitException
{
}
