<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParameterGroupNotFoundException extends NotFoundHttpException implements ParameterException
{
}
