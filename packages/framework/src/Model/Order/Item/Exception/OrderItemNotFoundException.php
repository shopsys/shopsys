<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderItemNotFoundException extends NotFoundHttpException
{
}
