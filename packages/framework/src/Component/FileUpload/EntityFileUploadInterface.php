<?php

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
    public function setFileAsUploaded($key, $originalFilename);

    public function getId(): int;
}
