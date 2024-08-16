<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComplaintNotFoundException extends NotFoundHttpException
{
}
