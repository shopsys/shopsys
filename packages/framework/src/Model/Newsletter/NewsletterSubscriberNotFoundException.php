<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsletterSubscriberNotFoundException extends NotFoundHttpException
{
}
