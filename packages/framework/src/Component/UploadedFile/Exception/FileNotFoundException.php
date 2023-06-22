<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
