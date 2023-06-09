<?php

declare(strict_types=1);

namespace App\Controller\Front;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        private readonly UploadedFileFacade $uploadedFileFacade,
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse
     */
    public function downloadAction(int $uploadedFileId, string $uploadedFilename): DownloadFileResponse
    {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        $uploadedFile = $this->uploadedFileFacade->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
        );
        $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new DownloadFileResponse(
            $uploadedFile->getNameWithExtension(),
            $this->filesystem->read($filePath),
            $this->filesystem->mimeType($filePath),
        );
    }
}
