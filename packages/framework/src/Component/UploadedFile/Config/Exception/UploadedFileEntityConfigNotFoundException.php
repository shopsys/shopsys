<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadedFileEntityConfigNotFoundException extends NotFoundHttpException implements UploadedFileConfigException
{
    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClassOrName, ?Exception $previous = null)
    {
        $message = sprintf('Not found uploaded file config for entity "%s"', $entityClassOrName);

        parent::__construct($message, $previous);
    }
}
