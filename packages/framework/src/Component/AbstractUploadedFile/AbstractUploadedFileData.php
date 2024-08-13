<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

class AbstractUploadedFileData
{
    /**
     * @var string[]
     */
    public $uploadedFiles = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface[]
     */
    public $orderedFiles = [];

    /**
     * @var string[]
     */
    public $currentFilenamesIndexedById = [];

    /**
     * @var string[]
     */
    public $uploadedFilenames = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface[]
     */
    public $filesToDelete = [];
}
