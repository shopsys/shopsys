<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileController
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    public function downloadAction(int $uploadedFileId, string $uploadedFilename): DownloadFileResponse
    {
        try {
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
        } catch (Exception $ex) { // never disclose backend error pages
            throw new NotFoundRedirectToStorefrontException(sprintf('File "%s" not found.', $uploadedFilename), $ex);
        }
    }
}
