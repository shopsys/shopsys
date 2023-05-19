<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AvailabilityNotFoundException extends NotFoundHttpException implements AvailabilityException
{
}
