<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileLocatorInterface
{
    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string;
}
