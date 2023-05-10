<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParameterValueNotFoundException extends NotFoundHttpException
{
}
