<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParameterNotFoundException extends NotFoundHttpException implements ParameterException
{
}
