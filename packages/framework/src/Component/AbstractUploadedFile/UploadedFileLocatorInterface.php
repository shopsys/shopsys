<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileLocatorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string;
}
