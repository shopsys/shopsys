<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

interface EntityFileUploadInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    public function getTemporaryFilesForUpload(): array;

    public function setFileAsUploaded(string $key, string $originalFilename): void;

    public function setFileKeyAsUploaded(string $key): void;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getFilesize();
}
