<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

class UploadedFileData
{
    /**
     * @var string[]
     */
    public $uploadedFiles = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public $filesToDelete = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public $orderedFiles = [];
}
