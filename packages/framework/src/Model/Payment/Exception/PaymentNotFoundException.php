<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentNotFoundException extends NotFoundHttpException implements PaymentException
{
}
