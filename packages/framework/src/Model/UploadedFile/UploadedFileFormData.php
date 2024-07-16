<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\UploadedFile;

class UploadedFileFormData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public $files;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $names = [];
}
