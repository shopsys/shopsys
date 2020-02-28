<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Exception;

use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParameterGroupNotFoundException extends NotFoundHttpException implements ParameterException
{
}
