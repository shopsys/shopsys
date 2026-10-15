<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaNotFoundException extends NotFoundHttpException
{
}
