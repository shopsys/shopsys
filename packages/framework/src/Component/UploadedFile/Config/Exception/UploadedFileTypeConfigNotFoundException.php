<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadedFileTypeConfigNotFoundException extends NotFoundHttpException implements UploadedFileConfigException
{
    /**
     * @param string $typeName
     * @param \Exception|null $previous
     */
    public function __construct(string $typeName, ?Exception $previous = null)
    {
        $message = sprintf('Uploaded file type config name "%s" not found.', $typeName);

        parent::__construct($message, $previous);
    }
}
