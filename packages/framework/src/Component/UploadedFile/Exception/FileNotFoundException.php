<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileNotFoundException extends NotFoundHttpException implements FileException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
