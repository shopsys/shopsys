<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderStatusNotFoundException extends NotFoundHttpException implements OrderStatusException
{
}
