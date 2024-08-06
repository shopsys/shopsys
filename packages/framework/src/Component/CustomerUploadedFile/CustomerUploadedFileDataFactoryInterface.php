<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig;

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
        string $type = CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): CustomerUploadedFileData;
}
