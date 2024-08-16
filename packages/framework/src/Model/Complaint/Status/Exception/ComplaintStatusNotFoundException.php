<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComplaintStatusNotFoundException extends NotFoundHttpException
{
}
