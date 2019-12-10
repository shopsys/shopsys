<?php

declare(strict_types=1);

namespace App\Controller\Front;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \League\Flysystem\FilesystemInterface $filesystem
     */
    public function __construct(
        UploadedFileFacade $uploadedFileFacade,
        FilesystemInterface $filesystem
    ) {
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->filesystem = $filesystem;
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFileName
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse
     */
    public function downloadAction(int $uploadedFileId, string $uploadedFileName): DownloadFileResponse
    {
        $uploadedFileSlug = pathinfo($uploadedFileName, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);

        $uploadedFile = $this->uploadedFileFacade->getByIdSlugAndExtension($uploadedFileId, $uploadedFileSlug, $uploadedFileExtension);
        $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new DownloadFileResponse(
            $uploadedFile->getNameWithExtension(),
            $this->filesystem->read($filePath),
            $this->filesystem->getMimetype($filePath)
        );
    }
}
