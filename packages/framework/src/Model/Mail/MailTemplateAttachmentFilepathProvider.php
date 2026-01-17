<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class MailTemplateAttachmentFilepathProvider
{
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly MountManager $mountManager,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function getTemporaryFilepath(UploadedFile $uploadedFile): string
    {
        $temporaryFilepath = $this->transformStringHelper->removeDriveLetterFromPath(
            $this->fileUpload->getAbsoluteTemporaryFilepath($uploadedFile->getFilename()),
        );

        if (!$this->mountManager->has('local://' . $temporaryFilepath)) {
            $uploadedFilePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            $this->mountManager->copy('main://' . $uploadedFilePath, 'local://' . $temporaryFilepath);
        }

        return $temporaryFilepath;
    }
}
