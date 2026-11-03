<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileFacade;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadedFileResponseFactory
{
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    public function createDownloadResponse(
        AbstractUploadedFileFacade $uploadedFileFacade,
        AbstractUploadedFile $uploadedFile,
    ): DownloadFileResponse {
        $filePath = $uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new DownloadFileResponse(
            $uploadedFile->getNameWithExtension(),
            $this->filesystem->read($filePath),
            $this->filesystem->mimeType($filePath),
        );
    }

    public function createInlineViewResponse(
        AbstractUploadedFileFacade $uploadedFileFacade,
        AbstractUploadedFile $uploadedFile,
    ): StreamedResponse {
        $filePath = $uploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new StreamedResponse(function () use ($filePath): void {
            $stream = $this->filesystem->readStream($filePath);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $uploadedFileFacade->getMimeTypeByExtension($uploadedFile),
            'Content-Length' => $this->filesystem->fileSize($filePath),
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $uploadedFile->getNameWithExtension()),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
