<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MailTemplateNotFoundException extends NotFoundHttpException implements MailException
{
}
