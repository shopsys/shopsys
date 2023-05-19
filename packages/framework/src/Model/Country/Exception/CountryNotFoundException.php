<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CountryNotFoundException extends NotFoundHttpException implements CountryException
{
}
