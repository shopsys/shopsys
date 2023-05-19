<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeliveryAddressNotFoundException extends NotFoundHttpException implements DeliveryAddressException
{
}
