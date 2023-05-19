<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderNotFoundException extends NotFoundHttpException implements OrderException
{
}
