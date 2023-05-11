<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class MailTemplateAttachmentFilepathProvider
{
    protected FileUpload $fileUpload;

    protected MountManager $mountManager;

    protected UploadedFileFacade $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(FileUpload $fileUpload, MountManager $mountManager, UploadedFileFacade $uploadedFileFacade)
    {
        $this->fileUpload = $fileUpload;
        $this->mountManager = $mountManager;
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getTemporaryFilepath(UploadedFile $uploadedFile): string
    {
        $temporaryFilepath = TransformString::removeDriveLetterFromPath(
            $this->fileUpload->getAbsoluteTemporaryFilepath($uploadedFile->getFilename())
        );

        if (!$this->mountManager->has('local://' . $temporaryFilepath)) {
            $uploadedFilePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            $this->mountManager->copy('main://' . $uploadedFilePath, 'local://' . $temporaryFilepath);
        }

        return $temporaryFilepath;
    }
}
