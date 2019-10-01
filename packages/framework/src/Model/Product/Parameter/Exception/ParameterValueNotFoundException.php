<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParameterValueNotFoundException extends NotFoundHttpException implements ParameterException
{
}
