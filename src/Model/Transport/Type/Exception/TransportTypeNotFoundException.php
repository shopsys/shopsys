<?php

declare(strict_types=1);

namespace App\Model\Transport\Type\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransportTypeNotFoundException extends NotFoundHttpException implements TransportTypeExceptionInterface
{
}
