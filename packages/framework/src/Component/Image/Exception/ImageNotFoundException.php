<?php

namespace Shopsys\FrameworkBundle\Component\Image\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageNotFoundException extends NotFoundHttpException implements ImageException
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
