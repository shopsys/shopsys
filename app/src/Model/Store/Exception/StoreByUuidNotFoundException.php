<?php

declare(strict_types=1);

namespace App\Model\Store\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreByUuidNotFoundException extends NotFoundHttpException implements StoreException
{
}
