<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig;

class UploadedFileService
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    public function __construct(
        FileUpload $fileUpload,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->fileUpload = $fileUpload;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    /**
     * @param int $entityId
     * @param string[] $temporaryFilenames
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function createUploadedFile(
        UploadedFileEntityConfig $uploadedFileEntityConfig,
        $entityId,
        array $temporaryFilenames
    ) {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath(array_pop($temporaryFilenames));

        return $this->uploadedFileFactory->create(
            $uploadedFileEntityConfig->getEntityName(),
            $entityId,
            pathinfo($temporaryFilepath, PATHINFO_BASENAME)
        );
    }
}
