<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Type\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransportTypeNotFoundException extends NotFoundHttpException
{
}
