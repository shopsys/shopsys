<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeliveryAddressNotFoundException extends NotFoundHttpException implements DeliveryAddressException
{
}
