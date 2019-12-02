<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

interface EntityFileUploadInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    public function getTemporaryFilesForUpload(): array;

    /**
     * @param string $key
     * @param string $originalFilename
     */
    public function setFileAsUploaded(string $key, string $originalFilename): void;

    /**
     * @return int
     */
    public function getId(): int;
}
