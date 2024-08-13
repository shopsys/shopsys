<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

interface CustomerUploadedFileDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public function create(): CustomerUploadedFileData;

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData
     */
    public function createByEntity(
        object $entity,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): CustomerUploadedFileData;
}
