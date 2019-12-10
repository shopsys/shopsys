<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

class DownloadFileResponse extends Response
{
    /**
     * @param string $filename
     * @param string $fileContent
     * @param string $mimeType
     */
    public function __construct(string $filename, string $fileContent, string $mimeType)
    {
        parent::__construct($fileContent);

        $this->headers->set('Content-type', $mimeType);
        $this->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
    }
}
