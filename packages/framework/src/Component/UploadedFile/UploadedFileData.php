<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileData;

/**
 * @property \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $orderedFiles
 * @property \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $filesToDelete
 */
class UploadedFileData extends AbstractUploadedFileData
{
    /**
     * @var array<int, array<string, string>>
     */
    public $names = [];

    /**
     * @var array<int, array<string, string>>
     */
    public $namesIndexedById = [];

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public $relations = [];

    /**
     * @var string[]
     */
    public $relationsFilenames = [];

    /**
     * @var array<int, array<string, string>>
     */
    public $relationsNames = [];
}
