<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PromoCodeNotFoundException extends NotFoundHttpException implements PromoCodeException
{
}
