<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

interface UploadedFileInterface
{
    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param string $temporaryFilename
     */
    public function setNameAndSlug(string $temporaryFilename): void;
}
