<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreByUuidNotFoundException extends NotFoundHttpException implements StoreException
{
}
