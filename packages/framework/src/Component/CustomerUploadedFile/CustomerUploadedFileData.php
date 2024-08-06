<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

class CustomerUploadedFileData
{
    /**
     * @var string[]
     */
    public $uploadedFiles = [];

    /**
     * @var string[]
     */
    public $uploadedFilenames = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    public $filesToDelete = [];

    /**
     * @var string[]
     */
    public $currentFilenamesIndexedById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    public $orderedFiles = [];
}
