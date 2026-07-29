<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFileId, $uploadedFilename);
            $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new DownloadFileResponse(
                $uploadedFile->getNameWithExtension(),
                $this->filesystem->read($filePath),
                $this->filesystem->mimeType($filePath),
            );
        } catch (Exception $e) {
            throw new NotFoundRedirectToStorefrontException(sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId), $e);
        }
    }

    public function viewAction(int $uploadedFileId, string $uploadedFilename): Response
    {
        try {
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFileId, $uploadedFilename);

            if (!$this->uploadedFileFacade->isUploadedFileViewableInBrowser($uploadedFile)) {
                return $this->downloadAction($uploadedFileId, $uploadedFilename);
            }

            $filePath = $this->uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new StreamedResponse(function () use ($filePath): void {
                $stream = $this->filesystem->readStream($filePath);
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $this->filesystem->mimeType($filePath),
                'Content-Length' => $this->filesystem->fileSize($filePath),
                'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $uploadedFile->getNameWithExtension()),
            ]);
        } catch (Exception $e) {
            throw new NotFoundRedirectToStorefrontException(sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId), $e);
        }
    }

    protected function getByIdSlugAndExtension(
        int $uploadedFileId,
        string $uploadedFilename,
    ): UploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        return $this->uploadedFileFacade->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
        );
    }
}
