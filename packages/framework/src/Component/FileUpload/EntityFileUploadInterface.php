<?php

namespace Shopsys\FrameworkBundle\Component\FileUpload;

interface EntityFileUploadInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    public function getTemporaryFilesForUpload(): array;
    
    public function setFileAsUploaded(string $key, string $originalFilename): void;

    public function getId(): int;
}
