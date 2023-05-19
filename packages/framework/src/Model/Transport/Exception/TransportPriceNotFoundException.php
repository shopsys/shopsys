<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransportPriceNotFoundException extends NotFoundHttpException implements TransportException
{
}
